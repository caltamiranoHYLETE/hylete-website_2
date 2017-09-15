<?php
/**
 * Copyright (c) 2017, Nosto Solutions Ltd
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 *
 * 3. Neither the name of the copyright holder nor the names of its contributors
 * may be used to endorse or promote products derived from this software without
 * specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @author Nosto Solutions Ltd <contact@nosto.com>
 * @copyright 2017 Nosto Solutions Ltd
 * @license http://opensource.org/licenses/BSD-3-Clause BSD 3-Clause
 *
 */



/**
 * Nosto account class for handling account related actions like, creation, OAuth2 syncing and SSO to Nosto.
 */
class Nosto_Object_Signup_Account extends Nosto_AbstractObject implements Nosto_Types_Signup_AccountInterface, Nosto_Types_ValidatableInterface
{
    /**
     * @var string the name of the Nosto account.
     */
    private $name;

    /**
     * @var Nosto_Request_Api_Token[] the Nosto API tokens associated with this account.
     */
    private $tokens = array();

    /**
     * Constructor.
     * Create a new account object with given name.
     *
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = $name;
        $this->validate();
    }

    /**
     * Validates the account attributes.
     *
     * @throws Nosto_NostoException if any attribute is invalid.
     */
    protected function validate()
    {
        $validator = new Nosto_Helper_ValidationHelper($this);
        if (!$validator->validate()) {
            foreach ($validator->getErrors() as $errors) {
                throw new Nosto_NostoException(sprintf('Invalid Nosto account. %s', $errors[0]));
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function isConnectedToNosto()
    {
        if (empty($this->tokens)) {
            return false;
        }
        foreach (Nosto_Request_Api_Token::getMandatoryApiTokenNames() as $name) {
            if ($this->getApiToken($name) === null) {
                return false;
            }
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getApiToken($name)
    {
        foreach ($this->tokens as $token) {
            if ($token->getName() === $name) {
                return $token;
            }
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function validationRules()
    {
        return array(
            array(array('name'), 'required')
        );
    }

    /**
     * Returns the account name.
     *
     * @return string the name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Returns the account tokens.
     *
     * @return Nosto_Request_Api_Token[] the tokens.
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * @param Nosto_Request_Api_Token[] $tokens
     */
    public function setTokens($tokens)
    {
        $this->tokens = $tokens;
    }

    /**
     * @inheritdoc
     */
    public function hasMissingTokens()
    {
        if (empty($this->tokens)) {
            return true;
        }
        foreach (Nosto_Request_Api_Token::getApiTokenNames() as $name) {
            if ($this->getApiToken($name) === null) {
                return true;
            }
        }
        return false;
    }

    /**
     * Adds an API token to the account.
     *
     * @param Nosto_Request_Api_Token $token the token.
     */
    public function addApiToken(Nosto_Request_Api_Token $token)
    {
        $this->tokens[] = $token;
    }

    /**
     * @inheritdoc
     */
    public function getMissingTokens()
    {
        $allTokens = Nosto_Request_Api_Token::getApiTokenNames();
        $missingTokens = array();
        foreach ($allTokens as $token) {
            if (!$this->getApiToken($token)) {
                $missingTokens[] = $token;
            }
        }

        return $missingTokens;
    }
}
