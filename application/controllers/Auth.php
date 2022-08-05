<?php
defined('BASEPATH') or exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;
use Firebase\JWT\JWT;

class Auth extends RestController
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('model_auth');
        date_default_timezone_set('Asia/Jakarta');
    }

    function generate_token($phone, $name)
    {
        $key = getenv('JWT_SECRET');

        $payload = array(
            'phone' => $phone,
            'name' => $name
        );

        $jwt = JWT::encode($payload, $key);

        $decoded = JWT::decode($jwt, $key, array('HS256'));

        return [$jwt, $decoded];
    }

    function register_post()
    {
        $phone = $this->input->post('phone');
        $name = $this->input->post('name');
        $bio = $this->input->post('bio');

        $created_by = 'SYS';
        $created_date = date('Y-m-d H:i:s');

        $this->form_validation->set_rules('phone', 'No. Telepon', 'required|trim|numeric');
        $this->form_validation->set_rules('name', 'Nama', 'required|trim');
        $this->form_validation->set_rules('bio', 'Biografi', 'trim');

        if ($this->form_validation->run() == FALSE) {
            $res = [
                'status' => FALSE,
                'msg' => strip_tags(validation_errors()),
                'data' => [
                    'input' => $this->input->post()
                ]
            ];
        } else {
            $check_phone = $this->model_auth->check_phone($phone);

            $count_phone = count($check_phone);

            if ($count_phone > 0) {
                $res = [
                    'status' => FALSE,
                    'msg' => 'Maaf! No. Telepon Anda sudah terdaftar',
                    'data' => [
                        'input' => $this->input->post()
                    ]
                ];
            } else {
                $data = array(
                    'phone' => $phone,
                    'name' => $name,
                    'bio' => $bio,
                    'created_by' => $created_by,
                    'created_date' => $created_date
                );

                $insert = $this->model_auth->insert('users', $data);

                if ($insert == TRUE) {
                    $res = [
                        'status' => TRUE,
                        'msg' => 'Berhasil! No. Telepon Anda sudah didaftarkan',
                        'data' => NULL
                    ];
                } else {
                    $res = [
                        'status' => FALSE,
                        'msg' => 'Maaf! No. Telepon Anda tidak berhasil didaftarkan. Silakkan coba kembali',
                        'data' => [
                            'input' => $this->input->post()
                        ]
                    ];
                }
            }
        }

        $this->response($res, 200);
    }

    function login_post()
    {
        $phone = $this->input->post('phone');

        $this->form_validation->set_rules('phone', 'No. Telepon', 'required|trim|numeric');

        if ($this->form_validation->run() == FALSE) {
            $res = [
                'status' => FALSE,
                'msg' => strip_tags(validation_errors()),
                'data' => [
                    'input' => $this->input->post()
                ]
            ];
        } else {
            $check_phone = $this->model_auth->check_phone($phone);

            $count_phone = count($check_phone);

            if ($count_phone > 0) {
                $phone = $check_phone['phone'];
                $name = $check_phone['name'];
                $bio = $check_phone['bio'];
                $photo = $check_phone['photo'];

                $token = $this->generate_token($phone, $name);

                $userAccount = array(
                    'phone' => $phone,
                    'name' => $name,
                    'bio' => $bio,
                    'photo' => $photo
                );

                $data = array(
                    'last_login' => date('Y-m-d H:i:s'),
                    'token' => $token[0]
                );

                $param = array('phone' => $phone);

                $process = $this->model_auth->update('users', $data, $param);

                if ($process == true) {
                    $res = [
                        'status' => TRUE,
                        'msg' => 'Berhasil! Selamat Datang di Aplikasi Chat Rakimin',
                        'data' => $userAccount,
                        'Token' => $token[0]
                    ];
                } else {
                    $res = [
                        'status' => FALSE,
                        'msg' => 'Maaf! Token Kadaluarsa. Silakkan Login kembali',
                        'data' => [
                            'input' => $this->input->post()
                        ]
                    ];
                }
            } else {
                $res = [
                    'status' => FALSE,
                    'msg' => 'Maaf! No. Telepon Anda tidak ditemukan.',
                    'data' => [
                        'input' => $this->input->post()
                    ]
                ];
            }
        }

        $this->response($res, 200);
    }
}
