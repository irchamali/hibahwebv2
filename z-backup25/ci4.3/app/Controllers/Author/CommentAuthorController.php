<?php

namespace App\Controllers\Author;

use App\Controllers\BaseController;
use App\Models\CommentModel;

class CommentAuthorController extends BaseController
{
    public function __construct()
    {
        $this->commentModel = new CommentModel();
    }
    public function index()
    {
        $data = [
            'akun' => $this->akun,
            'title' => 'All Comments',
            'active' => $this->active,
            'total_comment' => $this->commentModel->getCommentsAuthor(session('id'))->where('comment_status', 0)->get()->getNumRows(),
            'comments' => $this->commentModel->getCommentsAuthor(session('id'))->where('comment_status', 0)->get()->getResultArray(),
            'helper_text' => helper('text'),
            'breadcrumbs' => $this->request->getUri()->getSegments(),

            'all_comments' => $this->commentModel->getCommentsAuthor(session('id'))->where('comment_parent', 0)->get()->getResultArray(),
            'total_all_comments' => $this->commentModel->getCommentsAuthor(session('id'))->get()->getNumRows()
        ];

        return view('author/v_comment', $data);
    }
    public function reply()
    {
        $data = [
            'post_id' => htmlspecialchars($this->request->getPost('post_id')),
            'comment_id' => htmlspecialchars($this->request->getPost('comment_id')),
            'comments' => htmlspecialchars($this->request->getPost('comments'))
        ];
        $rules = [
            'post_id' => ['rules' => 'required|is_natural_no_zero|numeric'],
            'comment_id' => ['rules' => 'required|is_natural_no_zero|numeric'],
            'comments' => ['rules' => 'required|min_length[3]']
        ];
        if (!$this->validateData($data, $rules)) {
            return redirect()->to('/author/comment')->with('msg', 'not validated');
        }

        $validData = $this->validator->getValidated();
        $post_id = $validData['post_id'];
        $comment_id = $validData['comment_id'];
        $comment = $validData['comments'];
        $user_name = $this->akun['user_name'];
        $user_email = $this->akun['user_email'];
        $user_photo = $this->akun['user_photo'];
        $this->commentModel->save([
            'comment_name' => $user_name,
            'comment_email' => $user_email,
            'comment_message' => $comment,
            'comment_status' => 1,
            'comment_parent' => $comment_id,
            'comment_post_id' => $post_id,
            'comment_image' => $user_photo
        ]);
        return redirect()->to('/author/comment')->with('msg', 'success');
    }
    public function publish()
    {
        $comment_id = htmlspecialchars($this->request->getPost('comment_id4'));
        $this->commentModel->update($comment_id, ['comment_status' => 1]);
        return redirect()->to('/author/comment')->with('msg', 'success-publish');
    }
    public function edit()
    {
        $data = [
            'comment_id2' => htmlspecialchars($this->request->getPost('comment_id2')),
            'comments2' => htmlspecialchars($this->request->getPost('comments2'))
        ];
        $rules = [
            'comment_id2' => ['rules' => 'required|is_natural_no_zero|numeric'],
            'comments2' => ['rules' => 'required|min_length[3]']
        ];
        if (!$this->validateData($data, $rules)) {
            return redirect()->to('/author/comment')->with('msg', 'not validated');
        }
        $validData = $this->validator->getValidated();
        $comment_id2 = $validData['comment_id2'];
        $comment = $validData['comments2'];
        $this->commentModel->update($comment_id2, ['comment_message' => $comment]);
        return redirect()->to('/author/comment')->with('msg', 'success-edit');
    }
    public function delete()
    {
        $comment_id = $this->request->getPost('comment_id3');
        $this->commentModel->delete($comment_id);
        return redirect()->to('/author/comment')->with('msg', 'success-delete');
    }
    function unpublish()
    {
        $data = [
            'akun' => $this->akun,
            'title' => 'All Unpublish Comments',
            'active' => $this->active,
            'total_comment' => $this->commentModel->getCommentsAuthor(session('id'))->where('comment_status', 0)->get()->getNumRows(),
            'comments' => $this->commentModel->getCommentsAuthor(session('id'))->where('comment_status', 0)->get()->getResultArray(),
            'helper_text' => helper('text'),
            'breadcrumbs' => $this->request->getUri()->getSegments(),

            'all_comments' => $this->commentModel->getCommentsAuthor(session('id'))->get()->getResultArray(),
            'total_all_comments' => $this->commentModel->getCommentsAuthor(session('id'))->get()->getNumRows()
        ];

        return view('author/v_comment_unpublish', $data);
    }
}
