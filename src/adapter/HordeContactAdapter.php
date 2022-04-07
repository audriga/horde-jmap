<?php

namespace OpenXPort\Adapter;

use OpenXPort\Jmap\Contact\ContactInformation;
use OpenXPort\Jmap\Contact\Address;
use OpenXPort\Jmap\Contact\File;

class HordeContactAdapter extends AbstractAdapter
{
    /** @var VCard */
    private $vCard;

    public function getVCard()
    {
        return $this->vCard;
    }

    public function setVCard($vCard)
    {
        require_once __DIR__ . '/../../vcard/src/VCard.php';

        $this->vCard = $vCard;
    }

    public function getPrefix()
    {
        $prefix = $this->vCard->prefix;

        if (is_null($prefix) || !isset($prefix) || empty($prefix)) {
            return null;
        }

        return $prefix;
    }

    public function getFirstName()
    {
        $firstName = $this->vCard->firstname;

        if (is_null($firstName) || !isset($firstName) || empty($firstName)) {
            return null;
        }

        return $firstName;
    }

    public function getLastName()
    {
        $lastName = $this->vCard->lastname;

        if (is_null($lastName) || !isset($lastName) || empty($lastName)) {
            return null;
        }

        return $lastName;
    }

    public function getSuffix()
    {
        $suffix = $this->vCard->suffix;

        if (is_null($suffix) || !isset($suffix) || empty($suffix)) {
            return null;
        }

        return $suffix;
    }

    public function getNickname()
    {
        $nickname = $this->vCard->nickname;

        if (is_null($nickname) || !isset($nickname) || empty($nickname)) {
            return null;
        }

        return $nickname;
    }

    public function getBirthday()
    {
        $birthday = $this->vCard->birthday;

        if (is_null($birthday) || !isset($birthday) || empty($birthday)) {
            // This is the JMAP default value for the 'birthday' property
            return "0000-00-00";
        }

        return $birthday->format('Y-m-d');
    }

    public function getAnniversary()
    {
        $anniversary = $this->vCard->anniversary;

        if (is_null($anniversary) || !isset($anniversary) || empty($anniversary)) {
            // This is the JMAP default value for the 'anniversary' property, just like with the 'birthday' property
            return "0000-00-00";
        }

        return $anniversary->format('Y-m-d');
    }

    public function getCompany()
    {
        $org = $this->vCard->organization;

        if (!isset($org) || is_null($org)) {
            return null;
        }

        // The 'organization' property stores values <company>;<department>
        // That's why we explode the values by ';' and take the entry with index 0 for company
        return explode(';', $org)[0];
    }

    public function getDepartment()
    {
        $org = $this->vCard->organization;

        if (!isset($org) || is_null($org)) {
            return null;
        }

        // The 'organization' property stores values <company>;<department>
        // That's why we explode the values by ';' and take the entry with index 1 for department
        return explode(';', $org)[1];
    }

    public function getJobTitle()
    {
        $jobTitle = $this->vCard->title;

        if (is_null($jobTitle) || !isset($jobTitle) || empty($jobTitle)) {
            return null;
        }

        return $jobTitle;
    }

    public function getEmails()
    {
        if (!isset($this->vCard->email) || is_null($this->vCard->email)) {
            return null;
        }

        $jmapEmails = [];

        // The vCard from Horde contains only one single entry for email and it is of type 'INTERNET'
        // That's why we check for the 'INTERNET' type here
        if (isset($this->vCard->email['INTERNET']) && !empty($this->vCard->email['INTERNET'])) {
            foreach ($this->vCard->email['INTERNET'] as $email) {
                $jmapEmail = new ContactInformation();
                $jmapEmail->setType("home");
                $jmapEmail->setValue($email);
                $jmapEmail->setLabel(null);
                $jmapEmail->setIsDefault(true);

                array_push($jmapEmails, $jmapEmail);
            }
        }

        return $jmapEmails;
    }

    public function getPhones()
    {
        if (!isset($this->vCard->phone) || is_null($this->vCard->phone) || empty($this->vCard->phone)) {
            return null;
        }

        $jmapPhones = [];

        if (isset($this->vCard->phone['HOME,VOICE']) && !empty($this->vCard->phone['HOME,VOICE'])) {
            foreach ($this->vCard->phone['HOME,VOICE'] as $homePhone) {
                $jmapHomePhone = new ContactInformation();
                $jmapHomePhone->setType("home");
                $jmapHomePhone->setValue($homePhone);
                $jmapHomePhone->setLabel(null);
                $jmapHomePhone->setIsDefault(false);

                array_push($jmapPhones, $jmapHomePhone);
            }
        }

        if (isset($this->vCard->phone['WORK,VOICE']) && !empty($this->vCard->phone['WORK,VOICE'])) {
            foreach ($this->vCard->phone['WORK,VOICE'] as $workPhone) {
                $jmapWorkPhone = new ContactInformation();
                $jmapWorkPhone->setType("work");
                $jmapWorkPhone->setValue($workPhone);
                $jmapWorkPhone->setLabel(null);
                $jmapWorkPhone->setIsDefault(false);

                array_push($jmapPhones, $jmapWorkPhone);
            }
        }

        if (isset($this->vCard->phone['CELL,VOICE']) && !empty($this->vCard->phone['CELL,VOICE'])) {
            foreach ($this->vCard->phone['CELL,VOICE'] as $cellPhone) {
                $jmapCellPhone = new ContactInformation();
                $jmapCellPhone->setType("mobile");
                $jmapCellPhone->setValue($cellPhone);
                $jmapCellPhone->setLabel(null);
                $jmapCellPhone->setIsDefault(false);

                array_push($jmapPhones, $jmapCellPhone);
            }
        }

        if (isset($this->vCard->phone['FAX']) && !empty($this->vCard->phone['FAX'])) {
            foreach ($this->vCard->phone['FAX'] as $fax) {
                $jmapFax = new ContactInformation();
                $jmapFax->setType("fax");
                $jmapFax->setValue($fax);
                $jmapFax->setLabel(null);
                $jmapFax->setIsDefault(false);

                array_push($jmapPhones, $jmapFax);
            }
        }

        if (isset($this->vCard->phone['FAX,HOME']) && !empty($this->vCard->phone['FAX,HOME'])) {
            foreach ($this->vCard->phone['FAX,HOME'] as $homeFax) {
                $jmapHomeFax = new ContactInformation();
                $jmapHomeFax->setType("home");
                $jmapHomeFax->setValue($homeFax);
                $jmapHomeFax->setLabel(null);
                $jmapHomeFax->setIsDefault(false);

                array_push($jmapPhones, $jmapHomeFax);
            }
        }

        if (isset($this->vCard->phone['PAGER']) && !empty($this->vCard->phone['PAGER'])) {
            foreach ($this->vCard->phone['PAGER'] as $pager) {
                $jmapPager = new ContactInformation();
                $jmapPager->setType("pager");
                $jmapPager->setValue($pager);
                $jmapPager->setLabel(null);
                $jmapPager->setIsDefault(false);

                array_push($jmapPhones, $jmapPager);
            }
        }

        return $jmapPhones;
    }

    public function getOnline()
    {
        $jmapOnline = [];

        if (isset($this->vCard->url['default']) && !empty($this->vCard->url['default'])) {
            foreach ($this->vCard->url['default'] as $website) {
                $jmapWebsite = new ContactInformation();
                $jmapWebsite->setType("uri");
                $jmapWebsite->setLabel(null);
                $jmapWebsite->setValue($website);
                $jmapWebsite->setIsDefault(false);

                array_push($jmapOnline, $jmapWebsite);
            }
        }

        // Single IM address which Horde exports in vCard
        if (isset($this->vCard->xim) && !empty($this->vCard->xim)) {
            $jmapIm = new ContactInformation();
            $jmapIm->setType("username");
            $jmapIm->setLabel(null);
            $jmapIm->setValue($this->vCard->xim);
            $jmapIm->setIsDefault(false);

            array_push($jmapOnline, $jmapIm);
        }

        return $jmapOnline;
    }

    public function getAddresses()
    {
        if (!isset($this->vCard->address) || is_null($this->vCard->address) || empty($this->vCard->address)) {
            return null;
        }

        $jmapAddresses = [];

        if (isset($this->vCard->address['HOME']) && !empty($this->vCard->address['HOME'])) {
            foreach ($this->vCard->address['HOME'] as $homeAddress) {
                $jmapHomeAddress = new Address();
                $jmapHomeAddress->setType("home");
                $jmapHomeAddress->setLabel($homeAddress->extended);
                $jmapHomeAddress->setStreet($homeAddress->street);
                $jmapHomeAddress->setLocality($homeAddress->city);
                $jmapHomeAddress->setRegion($homeAddress->region);
                $jmapHomeAddress->setPostcode($homeAddress->zip);
                $jmapHomeAddress->setCountry($homeAddress->country);
                $jmapHomeAddress->setIsDefault(false);

                array_push($jmapAddresses, $jmapHomeAddress);
            }
        }

        if (isset($this->vCard->address['WORK']) && !empty($this->vCard->address['WORK'])) {
            foreach ($this->vCard->address['WORK'] as $workAddress) {
                $jmapWorkAddress = new Address();
                $jmapWorkAddress->setType("work");
                $jmapWorkAddress->setLabel($workAddress->extended);
                $jmapWorkAddress->setStreet($workAddress->street);
                $jmapWorkAddress->setLocality($workAddress->city);
                $jmapWorkAddress->setRegion($workAddress->region);
                $jmapWorkAddress->setPostcode($workAddress->zip);
                $jmapWorkAddress->setCountry($workAddress->country);
                $jmapWorkAddress->setIsDefault(false);

                array_push($jmapAddresses, $jmapWorkAddress);
            }
        }

        if (isset($this->vCard->address['OTHER']) && !empty($this->vCard->address['OTHER'])) {
            foreach ($this->vCard->address['OTHER'] as $otherAddress) {
                $jmapOtherAddress = new Address();
                $jmapOtherAddress->setType("other");
                $jmapOtherAddress->setLabel($otherAddress->extended);
                $jmapOtherAddress->setStreet($otherAddress->street);
                $jmapOtherAddress->setLocality($otherAddress->city);
                $jmapOtherAddress->setRegion($otherAddress->region);
                $jmapOtherAddress->setPostcode($otherAddress->zip);
                $jmapOtherAddress->setCountry($otherAddress->country);
                $jmapOtherAddress->setIsDefault(false);

                array_push($jmapAddresses, $jmapOtherAddress);
            }
        }

        return $jmapAddresses;
    }

    public function getNotes()
    {
        $notes = $this->vCard->note;

        if (is_null($notes) || !isset($notes) || empty($notes)) {
            return null;
        }

        return $notes;
    }

    public function getMiddlename()
    {
        $middlename = $this->vCard->additional;

        if (!isset($middlename) || is_null($middlename)) {
            return null;
        }

        return $middlename;
    }

    public function getRole()
    {
        $role = $this->vCard->role;

        if (!isset($role) || is_null($role)) {
            return null;
        }

        return $role;
    }

    public function getRelatedTo()
    {
        $jmapRelatedTo = [];

        $spouse = $this->vCard->spouse;

        if (isset($spouse)) {
            $jmapRelatedTo["$spouse"] = array("relation" => array("spouse" => true));
        }

        return $jmapRelatedTo;
    }

    public function getAvatar()
    {
        $jmapAvatar = null;

        if (isset($this->vCard->rawPhoto)) {
            $base64Avatar = base64_encode($this->vCard->rawPhoto);
            $jmapAvatar = new File();
            $jmapAvatar->setBase64($base64Avatar);
        }

        return $jmapAvatar;
    }

    public function getDisplayname()
    {
        $displayname = $this->vCard->fullname;

        if (!isset($displayname) || is_null($displayname)) {
            return null;
        }

        return $displayname;
    }
}
