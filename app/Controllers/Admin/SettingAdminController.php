<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CommentModel;
use App\Models\HomeModel;
use App\Models\InboxModel;
use App\Models\SiteModel;

class SettingAdminController extends BaseController
{
    public function __construct()
    {
        $this->inboxModel = new InboxModel();
        $this->commentModel = new CommentModel();

        $this->siteModel = new SiteModel();
        $this->homeModel = new HomeModel();
    }
    public function web()
    {

        $data = [
            'akun' => $this->akun,
            'title' => 'Website Setting',
            'active' => $this->active,
            'total_inbox' => $this->inboxModel->where('inbox_status', 0)->get()->getNumRows(),
            'inboxs' => $this->inboxModel->where('inbox_status', 0)->findAll(),
            'total_comment' => $this->commentModel->where('comment_status', 0)->get()->getNumRows(),
            'comments' => $this->commentModel->where('comment_status', 0)->findAll(6),
            'helper_text' => helper('text'),

            'sites' => $this->siteModel->first(),
            'validation' => \Config\Services::validation()
        ];

        return view('admin/v_setting-web', $data);
    }
    public function web_update()
    {
        // Validasi
        if (!$this->validate([
            'site_id' => [
                'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi!',
                    'numeric' => 'inputan harus angka'
                ]
            ],
            'name' => [
                'rules' => 'required|alpha_space',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi!',
                    'alpha_space' => 'inputan tidak boleh mengandung karakter aneh'
                ]
            ],
            'title' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi!'
                ]
            ],
            'description' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi!'
                ]
            ],
            'facebook' => [
                'rules' => 'required|valid_url_strict',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi!',
                    'valid_url_strict' => 'inputan harus berupa link'
                ]
            ],
            'twitter' => [
                'rules' => 'required|valid_url_strict',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi!',
                    'valid_url_strict' => 'inputan harus berupa link'
                ]
            ],
            'linkedin' => [
                'rules' => 'required|valid_url_strict',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi!',
                    'valid_url_strict' => 'inputan harus berupa link'
                ]
            ],
            'instagram' => [
                'rules' => 'required|valid_url_strict',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi!',
                    'valid_url_strict' => 'inputan harus berupa link'
                ]
            ],
            'pinterest' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi!'
                ]
            ],
            'wa' => [
                'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi!',
                    'numeric' => 'Iputan harus angka'
                ]
            ],
            'mail' => [
                'rules' => 'required|valid_email',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi!',
                    'valid_email' => 'Iputan harus email'
                ]
            ],
            'logo_icon' => [
                'rules' => 'max_size[logo_icon,2048]|is_image[logo_icon]|mime_in[logo_icon,image/jpg,image/jpeg,image/png]',
                'errors' => [
                    'max_size' => 'Ukuran gambar tidak boleh lebih dari 2MB',
                    'is_image' => 'Yang anda pilih bukan gambar',
                    'mime_in' => 'Yang anda pilih bukan gambar'
                ]
            ],
            'logo_header' => [
                'rules' => 'max_size[logo_icon,2048]|is_image[logo_icon]|mime_in[logo_icon,image/jpg,image/jpeg,image/png]',
                'errors' => [
                    'max_size' => 'Ukuran gambar tidak boleh lebih dari 2MB',
                    'is_image' => 'Yang anda pilih bukan gambar',
                    'mime_in' => 'Yang anda pilih bukan gambar'
                ]
            ], 'logo_big' => [
                'rules' => 'max_size[logo_icon,2048]|is_image[logo_icon]|mime_in[logo_icon,image/jpg,image/jpeg,image/png]',
                'errors' => [
                    'max_size' => 'Ukuran gambar tidak boleh lebih dari 2MB',
                    'is_image' => 'Yang anda pilih bukan gambar',
                    'mime_in' => 'Yang anda pilih bukan gambar'
                ]
            ]
        ])) {
            return redirect()->to("/admin/setting/web")->with('msg', 'error');
        }
        // Inisiasi
        $site_id = strip_tags(htmlspecialchars($this->request->getPost('site_id'), ENT_QUOTES));
        $name = strip_tags(htmlspecialchars($this->request->getPost('name'), ENT_QUOTES));
        $title = strip_tags(htmlspecialchars($this->request->getPost('title'), ENT_QUOTES));
        $description = strip_tags(htmlspecialchars($this->request->getPost('description'), ENT_QUOTES));
        $facebook = strip_tags(htmlspecialchars($this->request->getPost('facebook'), ENT_QUOTES));
        $instagram = strip_tags(htmlspecialchars($this->request->getPost('instagram'), ENT_QUOTES));
        $twitter = strip_tags(htmlspecialchars($this->request->getPost('twitter'), ENT_QUOTES));
        $linkedin = strip_tags(htmlspecialchars($this->request->getPost('linkedin'), ENT_QUOTES));
        $pinterest = $this->request->getPost('pinterest');
        $wa = strip_tags(htmlspecialchars($this->request->getPost('wa'), ENT_QUOTES));
        $mail = strip_tags(htmlspecialchars($this->request->getPost('mail'), ENT_QUOTES));

        // Cek Foto
        $data = $this->siteModel->find($site_id);
        $logoIconAwal = $data['site_favicon'];
        $logoHeaderAwal = $data['site_logo_header'];
        $logoBigAwal = $data['site_logo_big'];
        $fileLogoIcon = $this->request->getFile('logo_icon');
        $fileLogoHeader = $this->request->getFile('logo_header');
        $fileLogoBig = $this->request->getFile('logo_big');
        if ($fileLogoIcon->getName() == '') {
            $namaLogoIcon = $logoIconAwal;
        } else {
            $namaLogoIcon = $fileLogoIcon->getRandomName();
            $fileLogoIcon->move('assets/frontend/images/', $namaLogoIcon);
        }
        if ($fileLogoHeader->getName() == '') {
            $namaLogoHeader = $logoHeaderAwal;
        } else {
            $namaLogoHeader = $fileLogoHeader->getRandomName();
            $fileLogoHeader->move('assets/frontend/images/', $namaLogoHeader);
        }
        if ($fileLogoBig->getName() == '') {
            $namaLogoBig = $logoBigAwal;
        } else {
            $namaLogoBig = $fileLogoBig->getRandomName();
            $fileLogoBig->move('assets/frontend/images/', $namaLogoBig);
        }
        // Simpan ke database
        $this->siteModel->update($site_id, [
            'site_name' => $name,
            'site_title' => $title,
            'site_description' => $description,
            'site_favicon' => $namaLogoIcon,
            'site_logo_header' => $namaLogoHeader,
            'site_logo_big' => $namaLogoBig,
            'site_facebook' => $facebook,
            'site_twitter' => $twitter,
            'site_instagram' => $instagram,
            'site_linkedin' => $linkedin,
            'site_pinterest' => $pinterest,
            'site_wa' => $wa,
            'site_mail' => $mail,
        ]);
        return redirect()->to('/admin/setting/web')->with('msg', 'success');
    }
    public function home()
    {
        $data = [
            'akun' => $this->akun,
            'title' => 'Website Setting',
            'active' => $this->active,
            'total_inbox' => $this->inboxModel->where('inbox_status', 0)->get()->getNumRows(),
            'inboxs' => $this->inboxModel->where('inbox_status', 0)->findAll(),
            'total_comment' => $this->commentModel->where('comment_status', 0)->get()->getNumRows(),
            'comments' => $this->commentModel->where('comment_status', 0)->findAll(6),
            'helper_text' => helper('text'),

            'homes' => $this->homeModel->first(),
            'validation' => \Config\Services::validation()
        ];

        return view('admin/v_setting-home', $data);
    }
    public function home_update()
    {
        // Validasi
        if (!$this->validate([
            'home_id' => [
                'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi!',
                    'numeric' => 'inputan harus angka'
                ]
            ],
            'caption1' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi!',
                    'alpha_space' => 'inputan tidak boleh mengandung karakter aneh'
                ]
            ],
            'caption2' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi!'
                ]
            ],
            'img_heading' => [
                'rules' => 'max_size[img_heading,2048]|is_image[img_heading]|mime_in[img_heading,image/jpg,image/jpeg,image/png]',
                'errors' => [
                    'max_size' => 'Ukuran gambar tidak boleh lebih dari 2MB',
                    'is_image' => 'Yang anda pilih bukan gambar',
                    'mime_in' => 'Yang anda pilih bukan gambar'
                ]
            ],
            'img_testimonial' => [
                'rules' => 'max_size[img_testimonial,2048]|is_image[img_testimonial]|mime_in[img_testimonial,image/jpg,image/jpeg,image/png]',
                'errors' => [
                    'max_size' => 'Ukuran gambar tidak boleh lebih dari 2MB',
                    'is_image' => 'Yang anda pilih bukan gambar',
                    'mime_in' => 'Yang anda pilih bukan gambar'
                ]
            ],
            'img_testimonial2' => [
                'rules' => 'max_size[img_testimonial2,2048]|is_image[img_testimonial2]|mime_in[img_testimonial2,image/jpg,image/jpeg,image/png]',
                'errors' => [
                    'max_size' => 'Ukuran gambar tidak boleh lebih dari 2MB',
                    'is_image' => 'Yang anda pilih bukan gambar',
                    'mime_in' => 'Yang anda pilih bukan gambar'
                ]
            ]
        ])) {
            dd(\Config\Services::validation());
            return redirect()->to("/admin/setting/home")->with('msg', 'error');
        }
        // Inisiasi
        $home_id = strip_tags(htmlspecialchars($this->request->getPost('home_id'), ENT_QUOTES));
        $caption1 = strip_tags(htmlspecialchars($this->request->getPost('caption1'), ENT_QUOTES));
        $caption2 = strip_tags(htmlspecialchars($this->request->getPost('caption2'), ENT_QUOTES));

        // Cek Foto
        $data = $this->homeModel->find($home_id);
        $imgHeadingAwal = $data['home_bg_heading'];
        $imgTestimonialAwal = $data['home_bg_testimonial'];
        $imgTestimonial2Awal = $data['home_bg_testimonial2'];
        $fileImgHeading = $this->request->getFile('img_heading');
        $fileImgTestimonial = $this->request->getFile('img_testimonial');
        $fileImgTestimonial2 = $this->request->getFile('img_testimonial2');
        if ($fileImgHeading->getName() == '') {
            $namaImgHeading = $imgHeadingAwal;
        } else {
            $namaImgHeading = $fileImgHeading->getRandomName();
            $fileImgHeading->move('assets/frontend/img/', $namaImgHeading);
        }
        if ($fileImgTestimonial->getName() == '') {
            $namaImgTestimonial = $imgTestimonialAwal;
        } else {
            $namaImgTestimonial = $fileImgTestimonial->getRandomName();
            $fileImgTestimonial->move('assets/frontend/img/', $namaImgTestimonial);
        }
        if ($fileImgTestimonial2->getName() == '') {
            $namaImgTestimonial2 = $imgTestimonial2Awal;
        } else {
            $namaImgTestimonial2 = $fileImgTestimonial2->getRandomName();
            $fileImgTestimonial2->move('assets/frontend/img/', $namaImgTestimonial2);
        }
        // Simpan ke database
        $this->homeModel->update($home_id, [
            'home_caption_1' => $caption1,
            'home_caption_2' => $caption2,
            'home_bg_heading' => $namaImgHeading,
            'home_bg_testimonial' => $namaImgTestimonial,
            'home_bg_testimonial2' => $namaImgTestimonial2
        ]);
        return redirect()->to('/admin/setting/home')->with('msg', 'success');
    }
}
