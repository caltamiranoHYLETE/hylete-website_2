<?php

/**
 * Minimalistic Google OAuth2 connector
 * @author Mikuláš Dítě
 */
class Google
{

    const URL_AUTH  = 'https://accounts.google.com/o/oauth2/auth';
    const URL_TOKEN = 'https://accounts.google.com/o/oauth2/token';
    const URL_INFO  = 'https://www.googleapis.com/plus/v1/people/me';

    /** @var string client_id */
    private $id;

    /** @var string client_secret */
    private $secret;



    public function __construct(array $config)
    {
        $this->id = $config['id'];
        $this->secret = $config['secret'];
    }

    public function getLoginUrl(array $args)
    {
        $query = array(
            'response_type' => 'code',
            'client_id' => $this->id,
            'redirect_uri' => $args['redirect_uri'],
            'access_type' => 'online',
            'scope' => implode(' ', $args['scope']),
            //'approval_prompt' => 'force',
        );

        if (isset($args['state'])) {
            $query['state'] = $args['state'];
        }

        return self::URL_AUTH . '?' . http_build_query($query);
    }



    public function getToken($code, $uri)
    {
        $query = array(
            'code' => $code,
            'redirect_uri' => $uri,
            'client_id' => $this->id,
            'client_secret' => $this->secret,
            'grant_type' => 'authorization_code',
        );

        $content = http_build_query($query);
        $c = curl_init();
        curl_setopt_array($c, array(
            CURLOPT_URL => self::URL_TOKEN,
            CURLOPT_POST => TRUE,
            CURLOPT_POSTFIELDS => $content,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_HTTPHEADER => array(
                'Content-type: application/x-www-form-urlencoded',
                'Content-length: ' . strlen($content)
            ),
        ));
        $res = curl_exec($c);
        curl_close($c);
        $data = Mage::helper('core')->jsonDecode($res);

        if (isset($data['error'])) {
            throw new GoogleException("Error while obtaining token from code: " . $data['error']);
        }

        return isset($data['access_token']) ? $data['access_token'] : false;
    }



    public function getInfo($token)
    {
        $params = array(
            'access_token' => $token,
        );
        // $url = self::URL_INFO . '?' . http_build_query($params);
        $client = new Zend_Http_Client();
        $client->setUri(self::URL_INFO);
        $client->setConfig(array('maxredirects'=>0, 'timeout'=>30));
        $client->setParameterGet($params);
        $response = $client->request();
        $responseBody = $response->getBody();

        return Mage::helper('core')->jsonDecode($responseBody);
    }

}

class GoogleException extends RuntimeException {}
