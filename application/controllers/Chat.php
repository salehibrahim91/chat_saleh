<?php
defined('BASEPATH') or exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class Chat extends RestController
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('model_chat');
        date_default_timezone_set('Asia/Jakarta');
    }

    function choose_receiver_post()
    {
        $headers = $this->input->request_headers();
        $token = (isset($headers['Token'])) ? $headers['Token'] : FALSE;

        $now = date('Y-m-d');

        $sender = $this->input->post('sender');
        $receiver = $this->input->post('receiver');

        $created_date = date('Y-m-d H:i:s');

        $this->form_validation->set_rules('sender', 'Pengirim', 'required|trim|numeric');
        $this->form_validation->set_rules('receiver', 'Penerima', 'required|trim|numeric');

        if ($token) {
            $check_token = $this->model_chat->check_token($token);

            if ($check_token['cnt'] > 0) {
                $check_expired = $this->model_chat->check_expired($now, $token);

                if ($check_expired['expired'] > 7) {
                    $res = [
                        'status' => FALSE,
                        'msg' => 'Token Expired',
                        'msg_code' => '001'
                    ];
                } else {
                    if ($this->form_validation->run() == FALSE) {
                        $res = [
                            'status' => FALSE,
                            'msg' => strip_tags(validation_errors()),
                            'data' => [
                                'input' => $this->input->post()
                            ]
                        ];
                    } else {
                        $data = array(
                            'sender' => $sender,
                            'receiver' => $receiver,
                            'created_date' => $created_date
                        );

                        $insert = $this->model_chat->insert('chat', $data);

                        if ($insert == TRUE) {
                            $get = $this->model_chat->get_chat($sender, $receiver);

                            $data_chat = array(
                                'id_chat' => $get['id'],
                                'sender' => $get['sender'],
                                'receiver' => $get['receiver']
                            );

                            $res = [
                                'status' => TRUE,
                                'msg' => 'Berhasil! Obrolan berhasil dibuat',
                                'data' => $data_chat
                            ];
                        } else {
                            $res = [
                                'status' => FALSE,
                                'msg' => 'Maaf! Obrolan tidak berhasil dibuat. Silakkan coba kembali',
                                'data' => [
                                    'input' => $this->input->post()
                                ]
                            ];
                        }
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

    function chat_post()
    {
        $headers = $this->input->request_headers();
        $token = (isset($headers['Token'])) ? $headers['Token'] : FALSE;

        $now = date('Y-m-d');

        $id_chat = $this->input->post('id_chat');
        $message = $this->input->post('message');
        $created_date = date('Y-m-d H:i:s');

        $this->form_validation->set_rules('id_chat', 'ID', 'required|trim|numeric');
        $this->form_validation->set_rules('message', 'Pesan', 'required|trim');

        if ($token) {
            $check_token = $this->model_chat->check_token($token);

            if ($check_token['cnt'] > 0) {
                $check_expired = $this->model_chat->check_expired($now, $token);

                if ($check_expired['expired'] > 7) {
                    $res = [
                        'status' => FALSE,
                        'msg' => 'Token Expired',
                        'msg_code' => '001'
                    ];
                } else {
                    if ($this->form_validation->run() == FALSE) {
                        $res = [
                            'status' => FALSE,
                            'msg' => strip_tags(validation_errors()),
                            'data' => [
                                'input' => $this->input->post()
                            ]
                        ];
                    } else {
                        $detail = $this->model_chat->get_read_by($id_chat);

                        $read_by = $detail['receiver'];
                        $sender = $detail['sender'];

                        $created_by = $sender;

                        $data = array(
                            'id_chat' => $id_chat,
                            'message' => $message,
                            'read_by' => $read_by,
                            'created_by' => $created_by,
                            'created_date' => $created_date
                        );

                        $insert = $this->model_chat->insert('chat_detail', $data);

                        if ($insert == TRUE) {
                            $get = $this->model_chat->get_chat_detail($id_chat);

                            $data_chat_detail = array();

                            foreach ($get as $gt) {
                                $data_chat_detail[] = array(
                                    'sender' => $created_by,
                                    'message' => $gt['message'],
                                    'status' => (($gt['status'] == 0) ? 'Belum Dibaca Penerima' : 'Sudah Dibaca Penerima')
                                );
                            }

                            $res = [
                                'status' => TRUE,
                                'msg' => 'Berhasil! Obrolan berhasil dibuat',
                                'data' => $data_chat_detail
                            ];
                        } else {
                            $res = [
                                'status' => FALSE,
                                'msg' => 'Maaf! Pesan Anda tidak berhasil terkirim. Silakkan coba kembali',
                                'data' => [
                                    'input' => $this->input->post()
                                ]
                            ];
                        }
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

    function list_chat_post()
    {
        $headers = $this->input->request_headers();
        $token = (isset($headers['Token'])) ? $headers['Token'] : FALSE;

        $now = date('Y-m-d');

        $receiver = $this->input->post('receiver');

        $this->form_validation->set_rules('receiver', 'Penerima', 'required|trim|numeric');

        if ($token) {
            $check_token = $this->model_chat->check_token($token);

            if ($check_token['cnt'] > 0) {
                $check_expired = $this->model_chat->check_expired($now, $token);

                if ($check_expired['expired'] > 7) {
                    $res = [
                        'status' => FALSE,
                        'msg' => 'Token Expired',
                        'msg_code' => '001'
                    ];
                } else {
                    if ($this->form_validation->run() == FALSE) {
                        $res = [
                            'status' => FALSE,
                            'msg' => strip_tags(validation_errors()),
                            'data' => [
                                'input' => $this->input->post()
                            ]
                        ];
                    } else {
                        $get = $this->model_chat->get_list_chat($receiver);

                        $data_list_chat = array();

                        foreach ($get as $gt) {
                            $data_list_chat[] = array(
                                'id_chat' => $gt['id_chat'],
                                'message' => $gt['message'],
                                'status_read' => $gt['status_read'],
                                'name' => $gt['name']
                            );
                        }

                        $res = [
                            'status' => TRUE,
                            'msg' => NULL,
                            'data' => $data_list_chat
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

    function open_chat_post()
    {
        $headers = $this->input->request_headers();
        $token = (isset($headers['Token'])) ? $headers['Token'] : FALSE;

        $now = date('Y-m-d');

        $id_chat = $this->input->post('id_chat');
        $receiver = $this->input->post('receiver');

        $this->form_validation->set_rules('id_chat', 'ID', 'required|trim|numeric');
        $this->form_validation->set_rules('receiver', 'Penerima', 'required|trim|numeric');

        if ($token) {
            $check_token = $this->model_chat->check_token($token);

            if ($check_token['cnt'] > 0) {
                $check_expired = $this->model_chat->check_expired($now, $token);

                if ($check_expired['expired'] > 7) {
                    $res = [
                        'status' => FALSE,
                        'msg' => 'Token Expired',
                        'msg_code' => '001'
                    ];
                } else {
                    if ($this->form_validation->run() == FALSE) {
                        $res = [
                            'status' => FALSE,
                            'msg' => strip_tags(validation_errors()),
                            'data' => [
                                'input' => $this->input->post()
                            ]
                        ];
                    } else {
                        $data = array('status' => 1);
                        $param = array(
                            'id_chat' => $id_chat,
                            'read_by' => $receiver
                        );

                        $update = $this->model_chat->update('chat_detail', $data, $param);

                        if ($update == TRUE) {
                            $get = $this->model_chat->get_chat_detail($id_chat);

                            $data_chat_detail = array();

                            foreach ($get as $gt) {
                                $data_chat_detail[] = array(
                                    'sender' => $gt['created_by'],
                                    'message' => $gt['message'],
                                    'status' => (($gt['status'] == 0) ? 'Belum Dibaca Penerima' : 'Sudah Dibaca Penerima')
                                );
                            }

                            $res = [
                                'status' => TRUE,
                                'msg' => 'Berhasil! Obrolan berhasil dibuka',
                                'data' => $data_chat_detail
                            ];
                        } else {
                            $res = [
                                'status' => FALSE,
                                'msg' => 'Maaf! Anda gagal membuka pesan. Silakkan coba kembali',
                                'data' => [
                                    'input' => $this->input->post()
                                ]
                            ];
                        }
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

    function reply_chat_post()
    {
        $headers = $this->input->request_headers();
        $token = (isset($headers['Token'])) ? $headers['Token'] : FALSE;

        $now = date('Y-m-d');

        $id_chat = $this->input->post('id_chat');
        $message = $this->input->post('message');
        $sender_message = $this->input->post('sender_message');
        $created_date = date('Y-m-d H:i:s');

        $this->form_validation->set_rules('id_chat', 'ID', 'required|trim|numeric');
        $this->form_validation->set_rules('message', 'Pesan', 'required|trim');
        $this->form_validation->set_rules('sender_message', 'Pengirim', 'required|trim');

        if ($token) {
            $check_token = $this->model_chat->check_token($token);

            if ($check_token['cnt'] > 0) {
                $check_expired = $this->model_chat->check_expired($now, $token);

                if ($check_expired['expired'] > 7) {
                    $res = [
                        'status' => FALSE,
                        'msg' => 'Token Expired',
                        'msg_code' => '001'
                    ];
                } else {
                    if ($this->form_validation->run() == FALSE) {
                        $res = [
                            'status' => FALSE,
                            'msg' => strip_tags(validation_errors()),
                            'data' => [
                                'input' => $this->input->post()
                            ]
                        ];
                    } else {
                        $detail = $this->model_chat->get_read_by($id_chat);

                        $sender = $detail['sender'];
                        $receiver = $detail['receiver'];

                        if ($sender_message == $sender) {
                            $read_by = $receiver;
                        } else {
                            $read_by = $sender;
                        }

                        $created_by = $sender_message;

                        $data = array(
                            'id_chat' => $id_chat,
                            'message' => $message,
                            'read_by' => $read_by,
                            'created_by' => $created_by,
                            'created_date' => $created_date
                        );

                        $insert = $this->model_chat->insert('chat_detail', $data);

                        if ($insert == TRUE) {
                            $get = $this->model_chat->get_chat_detail($id_chat);

                            $data_chat_detail = array();

                            foreach ($get as $gt) {
                                $data_chat_detail[] = array(
                                    'sender' => $created_by,
                                    'message' => $gt['message'],
                                    'status' => (($gt['status'] == 0) ? 'Belum Dibaca Penerima' : 'Sudah Dibaca Penerima')
                                );
                            }

                            $res = [
                                'status' => TRUE,
                                'msg' => 'Berhasil! Respon Obrolan berhasil dibuat',
                                'data' => $data_chat_detail
                            ];
                        } else {
                            $res = [
                                'status' => FALSE,
                                'msg' => 'Maaf! Pesan Anda tidak berhasil terkirim. Silakkan coba kembali',
                                'data' => [
                                    'input' => $this->input->post()
                                ]
                            ];
                        }
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
}
