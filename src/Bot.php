<?php
namespace BotVisa;
class Bot
{

    /**
     *
     */
    const          DIR_COOKIE='src/logs/';
    public         $FILE_COOKIE_USER;
    public         $url;
    public  static $post_field_auth=[];

    /**
     * @param $email
     * @param $password
     */
    public function __construct($email, $password)
    {
        self::$post_field_auth= [
            'utf8'              => '✓',
            'user[email]'       => $email,
            'user[password]'    => $password,
            'policy_confirmed'  => 1,
            'commit'            => 'Sign In'
        ];
       $this->FILE_COOKIE_USER=self::DIR_COOKIE.'cookie_'.$email;
       $this->createCookieFile();
    }

    /**
     * @return void
     */
    public function start():void
    {
        $this->openSignIn();
    }

    /**
     * @return void
     */
    public  function createCookieFile():void
    {
       if(!file_exists($this->FILE_COOKIE_USER)){
           fopen($this->FILE_COOKIE_USER,'w');
       }
    }

    /**
     * @return void
     */
    public function openSignIn():void
    {
        $this->url='https://ais.usvisa-info.com/en-ca/niv/users/sign_in';

        $curl_options_array=[
            CURLOPT_URL            =>$this->url,
            CURLOPT_RETURNTRANSFER =>true,
            CURLOPT_HEADER         =>true,
            CURLOPT_COOKIEFILE     =>$this->FILE_COOKIE_USER,
            CURLOPT_COOKIEJAR      =>$this->FILE_COOKIE_USER,
            CURLOPT_HTTPHEADER     => [
                                        'User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36 Edg/115.0.1901.188',
                                      ],
        ];

        $html=$this->fileGetContents($curl_options_array);
        $csrfToken=self::getCSRFToken($html);
        $this->auth($csrfToken);
    }

    /**
     * @param $csrfToken
     * @return void
     */
    public  function auth($csrfToken):void
    {
        $this->url='https://ais.usvisa-info.com/en-ca/niv/users/sign_in';

        $curl_options_array=[
            CURLOPT_URL            =>$this->url,
            CURLOPT_RETURNTRANSFER =>true,
            CURLOPT_HEADER         =>true,
            CURLOPT_POST           =>true,
            CURLOPT_HTTPHEADER     =>[
                                        'X-CSRF-TOKEN: ' . $csrfToken,
                                        'Content-Type: application/x-www-form-urlencoded',
                                        'User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36 Edg/115.0.1901.188',
                                     ],
            CURLOPT_COOKIEFILE     =>$this->FILE_COOKIE_USER,
            CURLOPT_COOKIEJAR      =>$this->FILE_COOKIE_USER,
            CURLOPT_POSTFIELDS     =>self::getQueryString(self::$post_field_auth)
        ];

      $this->fileGetContents($curl_options_array);
      $this->acountPage();
    }

    /**
     * @return void
     */
    public  function acountPage():void
    {
        $this->url='https://ais.usvisa-info.com/en-ca/niv/account';

        $curl_options_array=[
            CURLOPT_URL            =>$this->url,
            CURLOPT_RETURNTRANSFER =>true,
            CURLOPT_POST           =>false,
      //    CURLOPT_HEADER        =>true,
            CURLOPT_HTTPHEADER     =>[
                                        'User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36 Edg/115.0.1901.188',
                                      ],
            CURLOPT_COOKIEFILE     =>$this->FILE_COOKIE_USER,
            CURLOPT_COOKIEJAR      =>$this->FILE_COOKIE_USER,

        ];


       $html=$this->fileGetContents($curl_options_array);
       $groupId=self::getGroupId($html);

       $this->groupPage($groupId);
    }

    /**
     * @param $groupId
     * @return void
     */
    public  function groupPage($groupId):void
    {
        $this->url='https://ais.usvisa-info.com/en-ca/niv/groups/'.$groupId;

        $curl_options_array=[
            CURLOPT_URL            =>$this->url,
            CURLOPT_RETURNTRANSFER =>true,
            CURLOPT_POST           =>false,
            CURLOPT_HEADER         =>true,
            CURLOPT_HTTPHEADER     =>[
                                       'User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36 Edg/115.0.1901.188',
                                      ],
            CURLOPT_COOKIEFILE     =>$this->FILE_COOKIE_USER,
            CURLOPT_COOKIEJAR      =>$this->FILE_COOKIE_USER,

        ];
        $html=$this->fileGetContents($curl_options_array);
        $idShedule=self::getIdShedule($html);

        $this->appointmentPage($idShedule);
    }


    public function appointmentPage($idShedule):void
    {
        $this->url='https://ais.usvisa-info.com/en-ca/niv/schedule/'.$idShedule.'/appointment';

        $curl_options_array=[
            CURLOPT_URL            =>$this->url,
            CURLOPT_RETURNTRANSFER =>true,
            CURLOPT_POST           =>false,
            CURLOPT_HEADER         =>true,
            CURLOPT_HTTPHEADER     =>[
                                       'User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36 Edg/115.0.1901.188',
                                      ],
            CURLOPT_COOKIEFILE     =>$this->FILE_COOKIE_USER,
            CURLOPT_COOKIEJAR      =>$this->FILE_COOKIE_USER,
        ];

        $this->fileGetContents($curl_options_array);
    }

    /**
     * @param $headers
     * @return string
     */

    public function fileGetContents($headers) :string
    {
        $ch=curl_init();
        curl_setopt_array($ch,$headers);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    /**
     * @param $html
     * @return string
     */
    static function getCSRFToken($html):string
    {
        $dom =new \DOMDocument();
        libxml_use_internal_errors(true); // Игнорировать ошибки парсинга
        $dom->loadHTML($html);
        libxml_clear_errors();
        $csrfToken='';
        $metaToken = $dom->getElementsByTagName('meta');

        foreach ($metaToken as $meta)
        {
            if ($meta->getAttribute('name') === 'csrf-token') {
                $csrfToken=$meta->getAttribute('content');
            }
        }
        return $csrfToken ;
    }

    /**
     * @param $data
     * @return string
     */
    static  function getQueryString($data):string
    {
        $queryString = http_build_query($data);

        $queryString = str_replace(
            ['user%5Bemail%5D', 'user%5Bpassword%5D', 'utf8%3D%3F'],
            ['user%5Bemail%5D', 'user%5Bpassword%5D', 'utf8=%E2%9C%93'],
            $queryString
        );
        $queryString = str_replace('%21', '!', $queryString);
        $queryString = str_replace('utf8=%3F', 'utf8=%E2%9C%93', $queryString);
        return  $queryString;
    }

    /**
     * @param $html
     * @return string
     *
     */
    static  function getGroupId($html):string
    {
        $groupId='';
        if (preg_match('/\/groups\/(\d+)/', $html, $matches))
        {
            $groupId = $matches[1];
            echo "Номер группы: " . $groupId;
        } else {
            echo "Номер группы не найден!";
        }
        return $groupId;
    }

    static function getIdShedule($html):string
    {
        $number='';
        if (preg_match('~/en-ca/niv/schedule/(\d+)/applicants/~', $html, $matches)) {
            $number = $matches[1]; // 50643598

        }
        return  $number;
    }

}