<?php

namespace tdt4237\webapp\controllers;

use tdt4237\webapp\models\Patent;
use tdt4237\webapp\controllers\UserController;
use tdt4237\webapp\models\Comment;
use tdt4237\webapp\validation\PatentValidation;

class PatentController extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }


    public function index()
    {
        $patent = $this->patentRepository->all();
        $patent->sortByDate();
        $this->render('patents.twig', ['patent' => $patent]);
    }

    public function show($patentId)
    {
        $patent = $this->patentRepository->find($patentId);
        //$user   = $this->userRepository ->findByUser($username);
        $request = $this->app->request;
        $message = $request->get('msg');
        $variables = [];

        if($message) {
            $variables['msg'] = $message;

        }

        $this->render('showpatent.twig', [
            'patent' => $patent,
            'flash' => $variables
        ]);

    }

    public function addComment($postId)
    {

        if(!$this->auth->guest()) {

            $comment = new Comment();
            $comment->setAuthor($_SESSION['user']);
            $comment->setText($this->app->request->post("text"));
            $comment->setDate(date("dmY"));
            $comment->setPost($postId);
            $this->commentRepository->save($comment);
            $this->app->redirect('/posts/' . $postId);
        }
        else {
            $this->app->redirect('/login');
            $this->app->flash('info', 'you must log in to do that');
        }

    }

    public function showNewPatentForm()
    {

        if ($this->auth->check()) {
            $username = $_SESSION['user'];
            $this->render('registerpatent.twig', ['username' => $username]);
        } else {

            $this->app->flash('error', "You need to be logged in to register a patent");
            $this->app->redirect("/");
        }

    }

    public function create()
    {
        if ($this->auth->guest()) {
            $this->app->flash("info", "You must be logged on to register a patent");
            $this->app->redirect("/login");
        } else {
            $request     = $this->app->request;
            $title       = $request->post('title');
            $description = $request->post('description');
            $company     = $request->post('company');
            $date        = date("dmY");
            $file = $this -> startUpload();

            $validation = new PatentValidation($title, $description);
            if ($validation->isGoodToGo()) {
                $patent = new Patent($company, $title, $description, $date, $file);
                $patent->setCompany($company);
                $patent->setTitle($title);
                $patent->setDescription($description);
                $patent->setDate($date);
                $patent->setFile($file);
                $savedPatent = $this->patentRepository->save($patent);
                $this->app->redirect('/patent/' . $savedPatent . '?msg="Patent succesfully registered');
            }
        }

            $this->app->flashNow('error', join('<br>', $validation->getValidationErrors()));
            $this->app->render('registerpatent.twig');
    }

    public function startUpload()
    { 
        if(isset($_POST['submit']))
        {
            $target_dir =  getcwd()."\web\uploads\\";
            $targetFile = $target_dir . basename($_FILES['uploaded']['name']);
            if(!move_uploaded_file($_FILES['uploaded']['tmp_name'], $targetFile))
            {
                $this->app->flash('info', 'The file was uploaded');
                return $targetFile;
            }
            else
            {
                $this->app->flash('error', 'The file was not uploaded');
            }
        }
    }
}

