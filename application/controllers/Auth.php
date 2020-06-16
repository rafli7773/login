<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library("form_validation");
    }

    public function index()
    {
        $this->form_validation->set_rules("email", "Email", "required|valid_email|trim");
        $this->form_validation->set_rules("password", "Password", "required|trim");

        if ($this->form_validation->run() == false) {
            $data['title'] = "Login Page";
            $this->load->view("templates/auth_header", $data);
            $this->load->view("auth/login", $data);
            $this->load->view("templates/auth_footer");
        } else {
            $this->_login();
        }
    }

    private function _login()
    {
        $email = htmlspecialchars($this->input->post("email", true));
        $password = $this->input->post("password", true);

        $user = $this->db->get_where("user", ["email" => $email])->row_array();
        if ($user) {
            if ($user['is_active'] == 1) {
                if (password_verify($password, $user['password'])) {
                    $data = [
                        "email" => $user['email'],
                        "role_id" => $user['role_id']
                    ];
                    $this->session->set_userdata($data);
                    if ($user['role_id'] == 1) {
                        redirect("Admin");
                    } else {
                        redirect("User");
                    }
                } else {
                    $this->session->set_flashdata("message", '<div class="alert alert-danger" role="alert">Wrong Password!</div>');
                    redirect("auth");
                }
            } else {
                $this->session->set_flashdata("message", '<div class="alert alert-danger" role="alert">This Email Not Activated</div>');
                redirect("auth");
            }
        } else {
            $this->session->set_flashdata("message", '<div class="alert alert-danger" role="alert">Email not registered!</div>');
            redirect("auth");
        }
    }

    public function registration()
    {
        $this->form_validation->set_rules("name", "Name", "required|trim");
        $this->form_validation->set_rules("email", "Email", "required|trim|valid_email");
        $this->form_validation->set_rules("password1", "Password", "required|trim|matches[password2]|min_length[5]", [
            "matches" => "Password don't Match!",
            "min_length" => "Password to Short!"
        ]);
        $this->form_validation->set_rules("password2", "Password", "required|trim|matches[password1]");

        if ($this->form_validation->run() == false) {
            $data['title'] = "User Registration";
            $this->load->view("templates/auth_header", $data);
            $this->load->view("auth/registration", $data);
            $this->load->view("templates/auth_footer");
        } else {
            $data = [
                "name" => htmlspecialchars($this->input->post('name', true)),
                "email" => htmlspecialchars($this->input->post('email', true)),
                "image" => "default.png",
                "password" => password_hash($this->input->post("password1"), PASSWORD_DEFAULT),
                "role_id" => 2,
                "is_active" => 1,
                "data_created" => time()
            ];
            $this->db->insert("user", $data);
            $this->session->set_flashdata("message", '<div class="alert alert-success" role="alert">Congratulation!, Your account has been created, Please Login!</div>');
            redirect("auth");
        }
    }

    public function logout()
    {
        $this->session->unset_userdata("email");
        $this->session->unset_userdata("role_id");
        $this->session->set_flashdata("message", '<div class="alert alert-success" role="alert">Your Account has been Logged out</div>');
        redirect("auth");
    }
}
