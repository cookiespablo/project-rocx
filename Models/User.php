<?php

class User extends Model
{

    protected $table = 'users';

    /**
     * @Type varchar(255)
     */
    protected $email;

    /**
     * @Type varchar(255)
     */
    protected $password;

    /**
     * @Type varchar(40)
     */
    protected $role;

    /**
     * @Type varchar(255)
     */
    protected $firstname;

    /**
     * @Type varchar(255)
     */
    protected $lastname;

    /**
     * @Type varchar(255)
     */
    protected $image;

    /**
     * @@Type boolean
     */
    protected $active = true;


    public function __construct()
    {

    }


    public function getFullName()
    {
        return $this->firstname . " " . $this->lastname;
    }


    private function setPassword($password)
    {
        $this->password = password_hash($password, PASSWORD_BCRYPT);
    }


    private function checkPassword($password)
    {
        return password_verify($password, $this->password);
    }


    public static function generateSalt()
    {
        return uniqid();
    }


    protected static function newModel($obj)
    {

        $email = $obj->email;

        $existing = User::findBy('email', $email);
        if(count($existing) > 0) return false;

        //Check if user is valid
        return true;

    }


    public static function register($form)
    {
        if($form['password'] !== $form['repeat']) App::addError("passwords do not match");
        if(strlen($form['password']) < 8) App::addError("password is too short");

        $user = new User();
        $user->email = $form['email'];
        $user->setPassword($form['password']);
        $user->role = 'user';
        $user->firstname = $form['firstname'];
        $user->lastname = $form['lastname'];
        $user->save();
        if($user->getId()) {
            App::setLoggedInUser($user);
            return $user;
        } else {
            return false;
        }
    }


    public static function login($form)
    {
        $email = $form['email'];
        $password = $form['password'];
        $users = self::findBy('email', $email);
        if (count($users) > 0) {
            $user = $users[0];
            if ($user->checkPassword($password)) {
                App::setLoggedInUser($user);
                return $user;
            }
        }
        App::addError("Combination does not exist");
        return false;
    }


    public static function updateUser($form)
    {
        $user = self::findById(App::$user->id);
        $user->firstname = $form['firstname'];
        $user->lastname = $form['lastname'];

        if ( !!$_FILES['image']['tmp_name']) {
            $fileParts = pathinfo($_FILES['image']['name']);

            if($user->image) {
                @unlink(Http::$dirroot.'public/images/'.$user->image);
            }

            $user->image = sha1($fileParts['filename'].microtime()).'.'.$fileParts['extension'];

            if(in_array($fileParts['extension'], ['jpg', 'jpeg', 'png'])) {
                if(move_uploaded_file($_FILES['image']['tmp_name'], Http::$dirroot.'public/images/'.$user->image)) {
                    // the file has been moved correctly

                }
            }
            else {
                // error this file ext is not allowed
            }
        }

        $user->save();
    }


    public static function loginForm()
    {
        $form = new Form();

        $form->addField((new FormField("email"))
            ->type("email")
            ->placeholder("E-mail")
            ->required());

        $form->addField((new FormField("password"))
            ->type("password")
            ->placeholder("Password")
            ->required());

        return $form->getHTML();
    }


    public static function registerForm()
    {
        $form = new Form();

        $form->addField((new FormField("email"))
            ->type("email")
            ->placeholder("E-mail")
            ->required());

        $form->addField((new FormField("password"))
            ->type("password")
            ->placeholder("Password")
            ->required());

        $form->addField((new FormField("repeat"))
            ->type("password")
            ->placeholder("Password repeat")
            ->required());

        $form->addField((new FormField("firstname"))
            ->placeholder("First name")
            ->required());

        $form->addField((new FormField("lastname"))
            ->placeholder("Last name")
            ->required());

        return $form->getHTML();
    }


    public static function editUserForm()
    {
        $user = User::findById(App::$user->id);

        $form = new Form();

        $form->addField((new FormField("email"))
            ->type("email")
            ->placeholder("E-mail")
            ->value($user->email)
            ->required());

        $form->addField((new FormField("image"))
            ->type("file")
            ->placeholder("Image")
            ->value($user->image));

        $form->addField((new FormField("firstname"))
            ->placeholder("First name")
            ->value($user->firstname)
            ->required());

        $form->addField((new FormField("lastname"))
            ->placeholder("Last name")
            ->value($user->lastname)
            ->required());

        return $form->getHTML();
    }

}
