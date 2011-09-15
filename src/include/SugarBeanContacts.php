<?php

class SugarBeanContacts extends SugarBean
{
    public function __construct()
    {
        parent::SugarBean();
    }
    
    public function getBeanContacts() {
        $beanContacts = array();

        $query = "  SELECT ear.bean_id, ea.email_address FROM email_addresses ea
                    LEFT JOIN email_addr_bean_rel ear ON ea.id = ear.email_address_id
                    WHERE ea.opt_out = 0 
                        AND ea.invalid_email = 0 
                        AND ear.bean_module = 'Contacts'
                        AND ear.primary_address = 1
                        AND ear.deleted = 0
                        AND ear.primary_address = 1";

        $result = $this->db->query($query);
        while($row = $this->db->fetchByAssoc($result)) {
            $beanContacts[] = $this->getBeanContact($row['bean_id']);
        }
        return $beanContacts;
    }
    
    public function getBeanContact($beanId)
    {
        $bean = $this->getBeanObject();
        $bean->retrieve($beanId);
        $bean->fill_in_additional_list_fields();            
        
        return $bean;
    }
    
    private function getBeanObject()
    {
        return new Contact();
    }
}