<?php

namespace OpenXPort\Mapper;

use OpenXPort\Jmap\Mail\Identity;

class HordeIdentityMapper extends AbstractMapper
{
    public function mapFromJmap($jmapData, $adapter)
    {
        // TODO: I am empty inside
    }

    public function mapToJmap($data, $adapter)
    {
        $list = [];

        foreach ($data as $i) {
            $adapter->setIdentity($i);

            $ji = new Identity();

            $ji->setId($adapter->getId());
            $ji->setName($adapter->getName());
            $ji->setEmail($adapter->getEmail());
            $ji->setReplyTo($adapter->getReplyTo());
            $ji->setBcc($adapter->getBcc());
            $ji->setTextSignature($adapter->getTextSignature());
            $ji->setHtmlSignature($adapter->getHtmlSignature());

            array_push($list, $ji);
        }

        return $list;
    }
}
