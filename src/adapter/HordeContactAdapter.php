<?php

use JeroenDesloovere\VCard\VCard;

use OpenXPort\Adapter\AbstractAdapter;
use Jmap\Contact\ContactInformation;
use Jmap\Contact\Address;

class HordeContactAdapter extends AbstractAdapter {
    
    /** @var VCard */
    private $vCard;

    public function getVCard() {
        return $this->vCard;
    }

    public function setVCard($vCard) {
        $this->vCard = $vCard;
    }

    public function getPrefix() {
        $prefix = $this->vCard->getProperties('JeroenDesloovere\VCard\Property\Name');

        if (!empty($prefix)) {
            // Use the reset() function, since we get an array with a single object inside, whose index isn't always 0 however
            // This way, with reset() we directly get the object without having to bother searching for the right index
            $prefix = reset($prefix)->getPrefix();
        }
        
        if (is_null($prefix) || !isset($prefix) || empty($prefix)) {
            return null;
        }
        
        return $prefix;
    }

    public function getFirstName() {
        
        $firstName = $this->vCard->getProperties('JeroenDesloovere\VCard\Property\Name');

        if (!empty($firstName)) {
            // The vCard lib seems to swap values for the 'name' vCard property, such that firstName is contained in additionalName,
            // lastName is contained in firstName and additionalName is contained in lastName.
            // This is the reason why we use the getAdditional() function to obtain the firstName value here
            $firstName = reset($firstName)->getAdditional();
        }

        if (is_null($firstName) || !isset($firstName) || empty($firstName)) {
            return null;
        }

        return $firstName;
    }

    public function getLastName() {
        $lastName = $this->vCard->getProperties('JeroenDesloovere\VCard\Property\Name');

        if (!empty($lastName)) {
            // Check the comment in the getFirstName() method
            $lastName = reset($lastName)->getFirstName();
        }

        if (is_null($lastName) || !isset($lastName) || empty($lastName)) {
            return null;
        }

        return $lastName;
    }

    public function getSuffix() {
        $suffix = $this->vCard->getProperties('JeroenDesloovere\VCard\Property\Name');
        
        if (!empty($suffix)) {
            $suffix = reset($suffix)->getSuffix();
        }

        if (is_null($suffix) || !isset($suffix) || empty($suffix)) {
            return null;
        }
        
        return $suffix;
    }

    public function getNickname() {
        $nickname = $this->vCard->getProperties('JeroenDesloovere\VCard\Property\Nickname');

        if (!empty($nickname)) {
            $nickname = reset($nickname)->getValue();
        }

        if (is_null($nickname) || !isset($nickname) || empty($nickname)) {
            return null;
        }

        return $nickname;
    }

    public function getBirthday() {
        $birthday = $this->vCard->getProperties('JeroenDesloovere\VCard\Property\Birthdate');
        
        if (!empty($birthday)) {
            $birthday = reset($birthday)->getValue();
        }

        if (is_null($birthday) || !isset($birthday) || empty($birthday)) {
            // This is the JMAP default value for the 'birthday' property
            return "0000-00-00";
        }

        return $birthday->format('Y-m-d');
    }

    public function getAnniversary() {
        $anniversary = $this->vCard->getProperties('JeroenDesloovere\VCard\Property\Anniversary');
        if (!empty($anniversary)) {
            $anniversary = reset($anniversary)->getValue();
        }

        if (is_null($anniversary) || !isset($anniversary) || empty($anniversary)) {
            // This is the JMAP default value for the 'anniversary' property, just like with the 'birthday' property
            return "0000-00-00";
        }

        return $anniversary->format('Y-m-d');
    }

    public function getCompany() {
        // TODO: Implement me
    }

    public function getDepartment() {
        // TODO: Implement me
    }

    public function getJobTitle() {
        $jobTitle = $this->vCard->getProperties('JeroenDesloovere\VCard\Property\Title');
        
        if (!empty($jobTitle)) {
            $jobTitle = reset($jobTitle)->getValue();
        }

        if (is_null($jobTitle) || !isset($jobTitle) || empty($jobTitle)) {
            return null;
        }

        return $jobTitle;
    }

    public function getEmails() {
        // TODO: Implement me
    }

    public function getPhones() {
        // TODO: Implement me
    }

    public function getOnline() {
        // TODO: Implement me
    }

    public function getAddresses() {
        // TODO: Implement me
    }

    public function getNotes() {
        $notes = $this->vCard->getProperties('JeroenDesloovere\VCard\Property\Note');
        
        if (!empty($notes)) {
            $notes = reset($notes)->getValue();
        }

        if (is_null($notes) || !isset($notes) || empty($notes)) {
            return null;
        }

        return $notes;
    }
}