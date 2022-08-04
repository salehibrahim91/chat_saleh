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

    function generate_token($cif_no, $cif_type, $nama, $email)
    {
        $key = getenv('JWT_SECRET');

        $payload = array(
            'cif_no' => $cif_no,
            'cif_type' => $cif_type,
            'nama' => $nama,
            'email' => $email
        );

        $jwt = JWT::encode($payload, $key);

        $decoded = JWT::decode($jwt, $key, array('HS256'));

        return [$jwt, $decoded];
    }

    function login_post()
    {
        $username = $this->input->post('username');
        $password = $this->input->post('password');

        $salt = 'microfinance';

        $check_username = $this->model_auth->check_username($username);

        $count_username = count($check_username);

        if ($count_username > 0) {
            $password = sha1($password . $salt);
            $check_account = $this->model_auth->check_account($username, $password);

            $count_account = count($check_account);

            if ($count_account > 0) {
                $cif_no = $check_account['cif_no'];
                $cif_type = $check_account['cif_type'];
                $nama = $check_account['name'];
                $email = $check_account['email'];

                $token = $this->generate_token($cif_no, $cif_type, $nama, $email);

                $userAccount = array(
                    'cif_no' => $cif_no,
                    'cif_type' => $cif_type,
                    'nama' => $nama,
                    'email' => $email
                );

                $data = array(
                    'last_login' => date('Y-m-d H:i:s'),
                    'token' => $token[0]
                );

                $param = array('cif_no' => $cif_no);

                $process = $this->model_auth->update('client_member', $data, $param);

                if ($process == true) {
                    $res = [
                        'status' => TRUE,
                        'msg' => 'Berhasil! Selamat Datang di Mobile AKR Syariah',
                        'data' => $userAccount,
                        'Token' => $token[0]
                    ];
                } else {
                    $res = [
                        'status' => FALSE,
                        'msg' => 'Maaf! Token Kadaluarsa. Silakkan Login kembali',
                        'data' => [
                            'username' => $username,
                            'password' => $password,
                            'input' => $this->input->post()
                        ]
                    ];
                }
            } else {
                $res = [
                    'status' => FALSE,
                    'msg' => 'Maaf! Password Anda salah. Silakkan lakukan Lupa Password',
                    'data' => [
                        'username' => $username,
                        'password' => $password,
                        'input' => $this->input->post()
                    ]
                ];
            }
        } else {
            $url = 'https://akr.sirkah.id/index.php/api_client/m_check_account';
            //$url = 'http://localhost/sirkah-akr/index.php/api_client/m_check_account';

            $password = sha1($password . $salt);

            $data_login = array(
                'username' => $username,
                'password' => $password
            );

            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_login);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

            $response_json = curl_exec($ch);

            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);

            $ret = json_decode($response_json);

            $datax = array(
                'data' => $ret,
                'message' => $httpcode
            );

            $status = $datax['data']->status;
            $message = $datax['data']->message;

            if ($status == 1) {
                $cif_no = $datax['data']->cif_no;
                $cif_type = $datax['data']->cif_type;
                $nama = $datax['data']->nama;
                $email = $datax['data']->email;

                $token = $this->generate_token($cif_no, $cif_type, $nama, $email);

                $userAccount = array(
                    'cif_no' => $cif_no,
                    'cif_type' => $cif_type,
                    'nama' => $nama,
                    'email' => $email,
                    'password' => $password
                );

                if ($email == null) {
                    $email = 'akrpusat@gmail.com';
                }

                $insert = array(
                    'cif_no' => $cif_no,
                    'cif_type' => $cif_type,
                    'name' => $nama,
                    'email' => $email,
                    'password' => $password,
                    'last_login' => date('Y-m-d H:i:s'),
                    'token' => $token[0]
                );

                $process = $this->model_auth->insert('client_member', $insert);

                if ($process == TRUE) {
                    $res = [
                        'status' => TRUE,
                        'msg' => $message,
                        'data' => $userAccount,
                        'Token' => $token[0]
                    ];
                } else {
                    $res = [
                        'status' => FALSE,
                        'msg' => 'Maaf! Login tidak berhasil. Silakkan coba lagi',
                        'data' => [
                            'username' => $username,
                            'password' => $password,
                            'input' => $this->input->post()
                        ]
                    ];
                }
            } else {
                $res = [
                    'status' => FALSE,
                    'msg' => $message,
                    'data' => [
                        'username' => $username,
                        'password' => $password,
                        'input' => $this->input->post()
                    ]
                ];
            }
        }

        $this->response($res, 200);
    }

    function forgot_post()
    {
        $username = $this->input->post('username');

        $check_username = $this->model_auth->check_username($username);

        $count_username = count($check_username);

        $config = array();

        $config['mailtype'] = 'html';
        $config['protocol'] = 'smtp';
        $config['smtp_host'] = 'mail.abdikertaraharja.co.id';
        $config['smtp_user'] = 'info@abdikertaraharja.co.id';
        $config['smtp_pass'] = 'InfoAKR123_';
        $config['smtp_port'] = '587';
        $config['newline'] = "\r\n";

        $token = substr(md5(sha1(rand())), 0, 10);

        $link = 'https://resetpassword.abdikertaraharja.co.id/?reset=' . $token;

        $requested_date = date('Y-m-d H:i:s');

        if ($count_username > 0) {
            $email = $check_username['email'];
            $name = $check_username['name'];

            if ($email == 'akrpusat@gmail.com') {
                $bcc = '';
            } else {
                $bcc = 'akrpusat@gmail.com';
            }

            $pesan = "<p>Assalamu'alaikum Wr.Wb, " . $name . "</p>";
            $pesan .= '<p>Silakkan klik link dibawah ini untuk mengganti Password Anda.<br />' . $link . '</p>';
            $pesan .= '<p>Kami sangat sarankan agar Password Baru tidak sama dengan Password Lama. Terima kasih</p>';
            $pesan .= "<p>Wassalamu'alaikum Wr.Wb</p>";

            $this->email->initialize($config);
            $this->email->from('info@abdikertaraharja.co.id', 'Info KSPPS Abdi Kerta Raharja');
            $this->email->to($email);
            $this->email->bcc($bcc);
            $this->email->subject('Informasi Perubahan Password');
            $this->email->message($pesan);

            if ($this->email->send()) {
                $insert = array(
                    'cif_no' => $username,
                    'token' => $token,
                    'requested_date' => $requested_date
                );

                $process = $this->model_auth->insert('client_forgot', $insert);

                if ($process == TRUE) {
                    $res = [
                        'status' => TRUE,
                        'msg' => 'Berhasil! Silakkan cek email Anda',
                        'data' => $insert
                    ];
                } else {
                    $res = [
                        'status' => FALSE,
                        'msg' => 'Maaf! Permintaan Password tidak berhasil. Silakkan dicoba lagi',
                        'data' => [
                            'username' => $username,
                            'email' => $email,
                            'input' => $this->input->post()
                        ]
                    ];
                }
            } else {
                $res = [
                    'status' => FALSE,
                    'msg' => $this->email->print_debugger(),
                    'data' => [
                        'username' => $username,
                        'input' => $this->input->post()
                    ]
                ];
            }
        } else {
            $url = 'https://akr.sirkah.id/index.php/api_client/m_forgot';
            //$url = 'http://localhost/sirkah-akr/index.php/api_client/m_forgot';

            $data_forgot = array('username' => $username);

            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_forgot);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

            $response_json = curl_exec($ch);

            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);

            $ret = json_decode($response_json);

            $datax = array(
                'data' => $ret,
                'message' => $httpcode
            );

            $status = $datax['data']->status;
            $message = $datax['data']->message;

            if ($status == 1) {
                $email = $datax['data']->email;
                $name = $datax['data']->name;

                if ($email == 'akrpusat@gmail.com') {
                    $bcc = '';
                } else {
                    $bcc = 'akrpusat@gmail.com';
                }

                $pesan = "<p>Assalamu'alaikum Wr.Wb, " . $name . "</p>";
                $pesan .= '<p>Silakkan klik link dibawah ini untuk mengganti Password Anda.<br />' . $link . '</p>';
                $pesan .= '<p>Kami sangat sarankan agar Password Baru tidak sama dengan Password Lama. Terima kasih</p>';
                $pesan .= "<p>Wassalamu'alaikum Wr.Wb</p>";

                $this->email->initialize($config);
                $this->email->from('info@abdikertaraharja.co.id', 'Info KSPPS Abdi Kerta Raharja');
                $this->email->to($email);
                $this->email->bcc($bcc);
                $this->email->subject('Informasi Perubahan Password');
                $this->email->message($pesan);

                if ($this->email->send()) {
                    $insert = array(
                        'cif_no' => $username,
                        'token' => $token,
                        'requested_date' => $requested_date
                    );

                    $process = $this->model_auth->insert('client_forgot', $insert);

                    if ($process == TRUE) {
                        $res = [
                            'status' => TRUE,
                            'msg' => 'Berhasil! Silakkan cek email Anda',
                            'data' => $insert
                        ];
                    } else {
                        $res = [
                            'status' => FALSE,
                            'msg' => 'Maaf! Permintaan Password tidak berhasil. Silakkan dicoba lagi',
                            'data' => [
                                'username' => $username,
                                'email' => $email,
                                'input' => $this->input->post()
                            ]
                        ];
                    }
                } else {
                    $res = [
                        'status' => FALSE,
                        'msg' => $this->email->print_debugger(),
                        'data' => [
                            'username' => $username,
                            'input' => $this->input->post()
                        ]
                    ];
                }
            } else {
                $res = [
                    'status' => FALSE,
                    'msg' => $message,
                    'data' => [
                        'username' => $username,
                        'input' => $this->input->post()
                    ]
                ];
            }
        }

        $this->response($res, 200);
    }
}
