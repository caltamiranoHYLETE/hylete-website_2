<?php

class Cybersource_Cybersource_Model_SOPWebMobile_Jwt_Manager
{
    const JWT_LIFETIME = 3600;

    /**
     * @param string $referenceId
     * @param Mage_Sales_Model_Quote $quote
     * @param string $cardBin
     * @return string
     */
    public function generate($referenceId, $quote, $cardBin)
    {
        $payloadBuilder = Mage::getModel('cybersourcesop/jwt_payload_builder');
        $helper = Mage::helper('cybersourcesop');

        $key = new \Lcobucci\JWT\Signer\Key($helper->getPaApiKey());
        $signer = new \Lcobucci\JWT\Signer\Hmac\Sha256();
        $tokenBuilder = new \Lcobucci\JWT\Builder();

        $currentTime = time();

        $jwt = $tokenBuilder
            ->identifiedBy(uniqid('jwt_'), true)
            ->issuedBy($helper->getPaApiId())
            ->issuedAt($currentTime)
            ->expiresAt($currentTime + self::JWT_LIFETIME)
            ->withClaim('OrgUnitId', $helper->getPaOrgId())
            ->withClaim('ReferenceId', $referenceId)
            ->withClaim('Payload', $payloadBuilder->build($quote, $cardBin))
            ->withClaim('ObjectifyPayload', true)
            ->getToken($signer, $key);

        return $jwt->__toString();
    }

    /**
     * @param string $jwt
     * @return \Lcobucci\JWT\Token
     */
    public function parse($jwt)
    {
        $tokenParser = new \Lcobucci\JWT\Parser();
        return $tokenParser->parse($jwt);
    }

    /**
     * @param \Lcobucci\JWT\Token $parsedToken
     * @return bool
     */
    public function validate($parsedToken)
    {
        $helper = Mage::helper('cybersourcesop');

        $key = new \Lcobucci\JWT\Signer\Key($helper->getPaApiKey());
        $signer = new \Lcobucci\JWT\Signer\Hmac\Sha256();

        if (! $parsedToken->verify($signer, $key)) {
            return false;
        }

        if ($parsedToken->isExpired()) {
            return false;
        }

        return true;
    }
}
