<?php
defined('BASEPATH') or exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class Information extends RestController
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('model_information');
        date_default_timezone_set('Asia/Jakarta');
    }

    function dashboard_post()
    {
        $headers = $this->input->request_headers();
        $token = (isset($headers['token'])) ? $headers['token'] : FALSE;

        $now = date('Y-m-d');

        $cif_no = $this->input->post('cif_no');
        $cif_type = $this->input->post('cif_type');

        if ($token) {
            $check_token = $this->model_information->check_token($token);

            if ($check_token['cnt'] > 0) {
                $check_expired = $this->model_information->check_expired($now, $token);

                if ($check_expired['expired'] > 7) {
                    $res = [
                        'status' => FALSE,
                        'msg' => 'Token Expired',
                        'msg_code' => '001'
                    ];
                } else {
                    $url = 'https://akr.sirkah.id/index.php/api_client/m_information';
                    //$url = 'http://localhost/sirkah-akr/index.php/api_client/m_information';

                    $data_cif = array(
                        'cif_no' => $cif_no,
                        'cif_type' => $cif_type
                    );

                    $ch = curl_init($url);

                    curl_setopt($ch, CURLOPT_POST, TRUE);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_cif);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

                    $response_json = curl_exec($ch);

                    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                    curl_close($ch);

                    $ret = json_decode($response_json);

                    $datax = array(
                        'data' => $ret,
                        'message' => $httpcode
                    );

                    $tabungan_sukarela = $datax['data']->tabungan_sukarela;

                    $memberSaldo = array('tabungan_sukarela' => 'Rp. ' . currency($tabungan_sukarela));

                    $update = array('saldo' => $tabungan_sukarela);
                    $param = array('cif_no' => $cif_no);

                    $process = $this->model_information->update('client_member', $update, $param);

                    if ($process == TRUE) {
                        $res = [
                            'status' => TRUE,
                            'msg' => 'Berhasil! Saldo Anda sebesar Rp. ' . currency($tabungan_sukarela),
                            'msg_code' => null,
                            'data' => $memberSaldo
                        ];
                    } else {
                        $res = [
                            'status' => FALSE,
                            'msg' => 'Maaf! Saldo tidak berhasil didapatkan',
                            'msg_code' => null,
                            'data' => [
                                'cif_no' => $cif_no,
                                'cif_type' => $cif_type,
                                'input' => $this->input->post()
                            ]
                        ];
                    }
                }
            } else {
                $res = [
                    'status' => FALSE,
                    'msg' => 'Token Invalid',
                    'msg_code' => '002'
                ];
            }
        } else {
            $res = [
                'status' => FALSE,
                'msg' => 'No Token Provided',
                'msg_code' => '003'
            ];
        }

        $this->response($res, 200);
    }

    function profile_post()
    {
        $headers = $this->input->request_headers();
        $token = (isset($headers['token'])) ? $headers['token'] : FALSE;

        $now = date('Y-m-d');

        $cif_no = $this->input->post('cif_no');
        $pass = $this->input->post('password');
        $repassword = $this->input->post('repassword');

        $salt = 'microfinance';

        $password = sha1($pass . $salt);

        $check_username = $this->model_information->check_username($cif_no);
        $email = $check_username['email'];
        $name = $check_username['name'];

        $config = array();

        $config['mailtype'] = 'html';
        $config['protocol'] = 'smtp';
        $config['smtp_host'] = 'mail.abdikertaraharja.co.id';
        $config['smtp_user'] = 'info@abdikertaraharja.co.id';
        $config['smtp_pass'] = 'InfoAKR123_';
        $config['smtp_port'] = '587';
        $config['newline'] = "\r\n";

        if ($token) {
            $check_token = $this->model_information->check_token($token);

            if ($check_token['cnt'] > 0) {
                $check_expired = $this->model_information->check_expired($now, $token);

                if ($check_expired['expired'] > 7) {
                    $res = [
                        'status' => FALSE,
                        'msg' => 'Token Expired',
                        'msg_code' => '001'
                    ];
                } else {
                    if ($pass == $repassword) {
                        $data = array(
                            'cif_no' => $cif_no,
                            'password' => $password
                        );

                        $update = array('password' => $password);
                        $param = array('cif_no' => $cif_no);

                        if ($email == 'akrpusat@gmail.com') {
                            $bcc = '';
                        } else {
                            $bcc = 'akrpusat@gmail.com';
                        }

                        $pesan = "<p>Assalamu'alaikum Wr.Wb, " . $name . "</p>";
                        $pesan .= '<p>Password Anda telah berhasil diubah, segera hubungi Kami jika bukan Anda yang melakukannya. Terima kasih</p>';
                        $pesan .= "<p>Wassalamu'alaikum Wr.Wb</p>";

                        $this->email->initialize($config);
                        $this->email->from('info@abdikertaraharja.co.id', 'Info KSPPS Abdi Kerta Raharja');
                        $this->email->to($email);
                        $this->email->bcc($bcc);
                        $this->email->subject('Informasi Status Perubahan Password');
                        $this->email->message($pesan);

                        if ($this->email->send()) {
                            $process = $this->model_information->update('client_member', $update, $param);

                            if ($process == TRUE) {
                                $res = [
                                    'status' => TRUE,
                                    'msg' => 'Berhasil! Password berhasil diubah',
                                    'msg_code' => null,
                                    'data' => $data
                                ];
                            } else {
                                $res = [
                                    'status' => FALSE,
                                    'msg' => 'Maaf! Password gagal diubah. Silakkan dicoba kembali',
                                    'msg_code' => null,
                                    'data' => [
                                        'cif_no' => $cif_no,
                                        'password' => $password,
                                        'input' => $this->input->post()
                                    ]
                                ];
                            }
                        } else {
                            $res = [
                                'status' => FALSE,
                                'msg' => $this->email->print_debugger(),
                                'msg_code' => null,
                                'data' => [
                                    'username' => $cif_no,
                                    'input' => $this->input->post()
                                ]
                            ];
                        }
                    } else {
                        $res = [
                            'status' => FALSE,
                            'msg' => 'Maaf! Password Baru dan Ulang Password tidak sama',
                            'msg_code' => null,
                            'data' => [
                                'cif_no' => $cif_no,
                                'password' => $pass,
                                'repassword' => $repassword,
                                'input' => $this->input->post()
                            ]
                        ];
                    }
                }
            } else {
                $res = [
                    'status' => FALSE,
                    'msg' => 'Token Invalid',
                    'msg_code' => '002'
                ];
            }
        } else {
            $res = [
                'status' => FALSE,
                'msg' => 'No Token Provided',
                'msg_code' => '003'
            ];
        }

        $this->response($res, 200);
    }

    function saldo_post()
    {
        $headers = $this->input->request_headers();
        $token = (isset($headers['token'])) ? $headers['token'] : FALSE;

        $cif_no = $this->input->post('cif_no');

        $now = date('Y-m-d');

        if ($token) {
            $check_token = $this->model_information->check_token($token);

            if ($check_token['cnt'] > 0) {
                $check_expired = $this->model_information->check_expired($now, $token);

                if ($check_expired['expired'] > 7) {
                    $res = [
                        'status' => FALSE,
                        'msg' => 'Token Expired',
                        'msg_code' => '001'
                    ];
                } else {
                    $url = 'https://akr.sirkah.id/index.php/api_client/m_saldo';
                    //$url = 'http://localhost/sirkah-akr/index.php/api_client/m_saldo';

                    $data_cif = array('cif_no' => $cif_no);

                    $ch = curl_init($url);

                    curl_setopt($ch, CURLOPT_POST, TRUE);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_cif);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

                    $response_json = curl_exec($ch);

                    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                    curl_close($ch);

                    $ret = json_decode($response_json);

                    $datax = array(
                        'data' => $ret,
                        'message' => $httpcode
                    );

                    $saldo_sda = $datax['data']->sda;
                    $saldo_simpok = $datax['data']->simpok;
                    $saldo_sukarela = $datax['data']->sukarela;

                    $memberSaldo = array(
                        'saldo_sda' => 'Rp. ' . currency($saldo_sda),
                        'saldo_simpok' => 'Rp. ' . currency($saldo_simpok),
                        'saldo_sukarela' => 'Rp. ' . currency($saldo_sukarela)
                    );

                    $res = [
                        'status' => TRUE,
                        'msg' => null,
                        'msg_code' => null,
                        'data' => $memberSaldo
                    ];
                }
            } else {
                $res = [
                    'status' => FALSE,
                    'msg' => 'Token Invalid',
                    'msg_code' => '002'
                ];
            }
        } else {
            $res = [
                'status' => FALSE,
                'msg' => 'No Token Provided',
                'msg_code' => '003'
            ];
        }

        $this->response($res, 200);
    }

    function financing_post()
    {
        $headers = $this->input->request_headers();
        $token = (isset($headers['token'])) ? $headers['token'] : FALSE;

        $cif_no = $this->input->post('cif_no');

        $now = date('Y-m-d');

        if ($token) {
            $check_token = $this->model_information->check_token($token);

            if ($check_token['cnt'] > 0) {
                $check_expired = $this->model_information->check_expired($now, $token);

                if ($check_expired['expired'] > 7) {
                    $res = [
                        'status' => FALSE,
                        'msg' => 'Token Expired',
                        'msg_code' => '001'
                    ];
                } else {
                    $url = 'https://akr.sirkah.id/index.php/api_client/m_financing';
                    //$url = 'http://localhost/sirkah-akr/index.php/api_client/m_financing';

                    $data_cif = array('cif_no' => $cif_no);

                    $ch = curl_init($url);

                    curl_setopt($ch, CURLOPT_POST, TRUE);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_cif);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

                    $response_json = curl_exec($ch);

                    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                    curl_close($ch);

                    $ret = json_decode($response_json);

                    $datax = array(
                        'data' => $ret,
                        'message' => $httpcode
                    );

                    $count = count($datax['data']);

                    $memberFinancing = array();

                    for ($i = 0; $i < $count; $i++) {
                        $pembiayaan_ke = $datax['data'][$i]->pembiayaan_ke;
                        $pokok = $datax['data'][$i]->pokok;
                        $margin = $datax['data'][$i]->margin;
                        $jangka_waktu = $datax['data'][$i]->jangka_waktu;
                        $status = $datax['data'][$i]->status;
                        $account_financing_no = $datax['data'][$i]->account_financing_no;

                        $memberFinancing[] = array(
                            'pembiayaan_ke' => $pembiayaan_ke,
                            'pokok' => 'Rp. ' . currency($pokok),
                            'margin' => 'Rp. ' . currency($margin),
                            'jangka_waktu' => $jangka_waktu,
                            'status' => $status,
                            'account_financing_no' => $account_financing_no
                        );
                    }

                    $res = [
                        'status' => TRUE,
                        'msg' => null,
                        'msg_code' => null,
                        'data' => $memberFinancing
                    ];
                }
            } else {
                $res = [
                    'status' => FALSE,
                    'msg' => 'Token Invalid',
                    'msg_code' => '002'
                ];
            }
        } else {
            $res = [
                'status' => FALSE,
                'msg' => 'No Token Provided',
                'msg_code' => '003'
            ];
        }

        $this->response($res, 200);
    }

    function card_post()
    {
        $headers = $this->input->request_headers();
        $token = (isset($headers['token'])) ? $headers['token'] : FALSE;

        $account_financing_no = $this->input->post('account_financing_no');

        $now = date('Y-m-d');

        if ($token) {
            $check_token = $this->model_information->check_token($token);

            if ($check_token['cnt'] > 0) {
                $check_expired = $this->model_information->check_expired($now, $token);

                if ($check_expired['expired'] > 7) {
                    $res = [
                        'status' => FALSE,
                        'msg' => 'Token Expired',
                        'msg_code' => '001'
                    ];
                } else {
                    $url = 'https://akr.sirkah.id/index.php/api_client/m_card';
                    //$url = 'http://localhost/sirkah-akr/index.php/api_client/m_card';

                    $data_financing = array('account_financing_no' => $account_financing_no);

                    $ch = curl_init($url);

                    curl_setopt($ch, CURLOPT_POST, TRUE);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_financing);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

                    $response_json = curl_exec($ch);

                    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                    curl_close($ch);

                    $ret = json_decode($response_json);

                    $datax = array(
                        'data' => $ret,
                        'message' => $httpcode
                    );

                    $count = count($datax['data']->dataz);

                    $memberCard = array();

                    if ($datax['data']->status == 1) {
                        for ($i = 0; $i < $count; $i++) {
                            $angsuran_ke = $datax['data']->dataz[$i]->angsuran_ke;
                            $account_financing_no = $datax['data']->dataz[$i]->account_financing_no;
                            $saldo_pokok = $datax['data']->dataz[$i]->saldo_pokok;
                            $saldo_margin = $datax['data']->dataz[$i]->saldo_margin;
                            $saldo_simsus = $datax['data']->dataz[$i]->saldo_simsus;
                            $angsuran = $datax['data']->dataz[$i]->angsuran;
                            $tanggal_bayar = $datax['data']->dataz[$i]->tanggal_bayar;

                            $memberCard[] = array(
                                'angsuran_ke' => $angsuran_ke,
                                'account_financing_no' => $account_financing_no,
                                'saldo_pokok' => 'Rp. ' . currency($saldo_pokok),
                                'saldo_margin' => 'Rp. ' . currency($saldo_margin),
                                'saldo_simsus' => 'Rp. ' . currency($saldo_simsus),
                                'angsuran' => 'Rp. ' . currency($angsuran),
                                'tanggal_bayar' => $tanggal_bayar
                            );
                        }

                        $res = [
                            'status' => TRUE,
                            'msg' => null,
                            'msg_code' => null,
                            'data' => $memberCard
                        ];
                    } else {
                        $res = [
                            'status' => FALSE,
                            'msg' => $datax['data']->message,
                            'msg_code' => null
                        ];
                    }
                }
            } else {
                $res = [
                    'status' => FALSE,
                    'msg' => 'Token Invalid',
                    'msg_code' => '002'
                ];
            }
        } else {
            $res = [
                'status' => FALSE,
                'msg' => 'No Token Provided',
                'msg_code' => '003'
            ];
        }

        $this->response($res, 200);
    }

    function saving_post()
    {
        $headers = $this->input->request_headers();
        $token = (isset($headers['token'])) ? $headers['token'] : FALSE;

        $cif_no = $this->input->post('cif_no');

        $now = date('Y-m-d');

        if ($token) {
            $check_token = $this->model_information->check_token($token);

            if ($check_token['cnt'] > 0) {
                $check_expired = $this->model_information->check_expired($now, $token);

                if ($check_expired['expired'] > 7) {
                    $res = [
                        'status' => FALSE,
                        'msg' => 'Token Expired',
                        'msg_code' => '001'
                    ];
                } else {
                    $url = 'https://akr.sirkah.id/index.php/api_client/m_saving';
                    //$url = 'http://localhost/sirkah-akr/index.php/api_client/m_saving';

                    $data_cif = array('cif_no' => $cif_no);

                    $ch = curl_init($url);

                    curl_setopt($ch, CURLOPT_POST, TRUE);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_cif);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

                    $response_json = curl_exec($ch);

                    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                    curl_close($ch);

                    $ret = json_decode($response_json);

                    $datax = array(
                        'data' => $ret,
                        'message' => $httpcode
                    );

                    $count = count($datax['data']);

                    $memberSaving = array();

                    for ($i = 0; $i < $count; $i++) {
                        $product_name = $datax['data'][$i]->product_name;
                        $account_saving_no = $datax['data'][$i]->account_saving_no;
                        $saldo = $datax['data'][$i]->saldo;
                        $cif_type = $datax['data'][$i]->cif_type;

                        $memberSaving[] = array(
                            'product_name' => $product_name,
                            'account_saving_no' => $account_saving_no,
                            'saldo' => 'Rp. ' . currency($saldo),
                            'cif_type' => $cif_type
                        );
                    }

                    $res = [
                        'status' => TRUE,
                        'msg' => null,
                        'msg_code' => null,
                        'data' => $memberSaving
                    ];
                }
            } else {
                $res = [
                    'status' => FALSE,
                    'msg' => 'Token Invalid',
                    'msg_code' => '002'
                ];
            }
        } else {
            $res = [
                'status' => FALSE,
                'msg' => 'No Token Provided',
                'msg_code' => '003'
            ];
        }

        $this->response($res, 200);
    }

    function mutation_post()
    {
        $headers = $this->input->request_headers();
        $token = (isset($headers['token'])) ? $headers['token'] : FALSE;

        $product_name = $this->input->post('product_name');
        $account_saving_no = $this->input->post('account_saving_no');
        $cif_type = $this->input->post('cif_type');
        $from_date = $this->input->post('from_date');
        $thru_date = $this->input->post('thru_date');

        $from_date = str_replace('/', '-', $from_date);
        $thru_date = str_replace('/', '-', $thru_date);

        $from_date = date('Y-m-d', strtotime($from_date));
        $thru_date = date('Y-m-d', strtotime($thru_date));

        $now = date('Y-m-d');

        if ($token) {
            $check_token = $this->model_information->check_token($token);

            if ($check_token['cnt'] > 0) {
                $check_expired = $this->model_information->check_expired($now, $token);

                if ($check_expired['expired'] > 7) {
                    $res = [
                        'status' => FALSE,
                        'msg' => 'Token Expired',
                        'msg_code' => '001'
                    ];
                } else {
                    $url = 'https://akr.sirkah.id/index.php/api_client/m_mutation';
                    //$url = 'http://localhost/sirkah-akr/index.php/api_client/m_mutation';

                    $data_saving = array(
                        'product_name' => $product_name,
                        'account_saving_no' => $account_saving_no,
                        'cif_type' => $cif_type,
                        'from_date' => $from_date,
                        'thru_date' => $thru_date
                    );

                    $ch = curl_init($url);

                    curl_setopt($ch, CURLOPT_POST, TRUE);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_saving);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

                    $response_json = curl_exec($ch);

                    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                    curl_close($ch);

                    $ret = json_decode($response_json);

                    $datax = array(
                        'data' => $ret,
                        'message' => $httpcode
                    );

                    $count = count($datax['data']->dataz);

                    $mutationCard = array();

                    for ($i = 0; $i < $count; $i++) {
                        $transaction_date = $datax['data']->dataz[$i]->transaction_date;
                        $amount = $datax['data']->dataz[$i]->amount;
                        $description = $datax['data']->dataz[$i]->description;
                        $saldo = $datax['data']->dataz[$i]->saldo;

                        $mutationCard[] = array(
                            'transaction_date' => $transaction_date,
                            'amount' => 'Rp. ' . $amount,
                            'description' => $description,
                            'saldo' => 'Rp. ' . $saldo
                        );
                    }

                    $res = [
                        'status' => TRUE,
                        'msg' => null,
                        'msg_code' => null,
                        'data' => $mutationCard
                    ];
                }
            } else {
                $res = [
                    'status' => FALSE,
                    'msg' => 'Token Invalid',
                    'msg_code' => '002'
                ];
            }
        } else {
            $res = [
                'status' => FALSE,
                'msg' => 'No Token Provided',
                'msg_code' => '003'
            ];
        }

        $this->response($res, 200);
    }
}
