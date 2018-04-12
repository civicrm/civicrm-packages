<?php
/*
 * The MIT License
 *
 * Copyright (c) 2012 Shuhei Tanuma
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 */

/**
 * Class Jira_Api_Authentication_Basic
 */
class Jira_Api_Authentication_Basic implements Jira_Api_Authentication_AuthenticationInterface
{
    private $user_id;
    private $password;

    /**
     * Jira_Api_Authentication_Basic constructor.
     *
     * @param $user_id
     * @param $password
     */
    public function __construct($user_id, $password)
    {
        $this->user_id  = $user_id;
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getCredential()
    {
        return base64_encode($this->user_id . ':' . $this->password);
    }

    public function getId()
    {
        return $this->user_id;
    }

    public function getPassword()
    {
        return $this->password;
    }

}