<?php

namespace OpenXPort\Adapter;

class HordeIdentityAdapter extends AbstractAdapter
{
    private $identity;

    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * Use this function in order to avoid using a constructor which accepts args,
     * since we need an empty constructor for initialization of this class in the $dataAdapters array (in jmap.php)
     */
    public function setIdentity($identity)
    {
        $this->identity = $identity;
    }

    public function getId()
    {
        return $this->identity["id"];
    }

    public function getName()
    {
        return $this->identity["name"];
    }

    public function getEmail()
    {
        return $this->identity["email"];
    }

    public function getReplyTo()
    {
        if (is_null($this->identity['replyTo']) || empty($this->identity['replyTo'])) {
            return null;
        }

        $jmapReplyTo = [];

        foreach ($this->identity["replyTo"] as $hordeMail) {
            $jmapMail = new \OpenXPort\Jmap\Mail\EmailAddress();
            $jmapMail->setEmail($hordeMail);
            array_push($jmapReplyTo, $jmapMail);
        }


        return $jmapReplyTo;
    }

    public function getBcc()
    {
        if (is_null($this->identity['bcc']) || empty($this->identity['bcc'])) {
            return null;
        }

        $jmapBcc = [];

        foreach ($this->identity["bcc"] as $hordeMail) {
            $jmapMail = new \OpenXPort\Jmap\Mail\EmailAddress();
            $jmapMail->setEmail($hordeMail);
            array_push($jmapBcc, $jmapMail);
        }

        return $jmapBcc;
    }

    public function getTextSignature()
    {
        return $this->identity["textSignature"];
    }

    public function getHtmlSignature()
    {
        return $this->identity["htmlSignature"];
    }
}
