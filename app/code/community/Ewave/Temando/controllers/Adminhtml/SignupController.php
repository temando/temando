<?php

class Ewave_Temando_Adminhtml_SignupController extends Mage_Adminhtml_Controller_Action
{
    
    public function indexAction()
    {
        if (!Mage::helper('temando')->canSignUp()) {
            $this->_getSession()->addNotice($this->__('Profile already exists.'));
        }

        if ($form_data = $this->_getSession()->getTemandoSignupFormData()) {
            Mage::register('temando_sign_up_form_data', $form_data);
        }
        
        $this
            ->loadLayout()
            ->_setActiveMenu('temando/signup')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Sign Up'), Mage::helper('adminhtml')->__('Sign Up'))
            ->renderLayout();
    }
    
    public function saveAction()
    {
        if (Mage::helper('temando')->canSignUp() && ($form_data = $this->getRequest()->getParams())) {
            $form = Mage::getModel('temando/signup_form');
            /* @var $form Ewave_Temando_Model_Signup_Form */
            $form->setData($form_data);
            
            try {
                /*if (Mage::helper('temando')->getConfigData('general/sandbox')) {
                    throw new Exception('This function do not works for Sandbox API. Please, change your settings first');
                }*/
                $form->submit();
                /*if (Mage::helper('temando')->getConfigData('general/username')) {
                    $this->_getSession()
                        ->addSuccess(Mage::helper('temando')->__('Success. Profile updated.'))
                        ->setTemandoSignupFormData(false);
                } else {*/
                    $this->_getSession()
                        ->addSuccess(Mage::helper('temando')->__('Success. You should go to temando.com, login and register payment details or purchase credit in order to book live transactions.'))
                        ->setTemandoSignupFormData(false);
                /*}*/
            } catch (Ewave_Temando_Model_Signup_Form_Exception $ex) {
                foreach ($ex->getErrors() as $error) {
                    $this->_getSession()
                        ->addError($error)
                        ->setTemandoSignupFormData($form_data);
                }
            } catch (Exception $ex) {
                $error_message = $ex->getMessage();
                $this->_getSession()
                    ->addError($error_message)
                    ->setTemandoSignupFormData($form_data);
            }
        }
        $this->_redirect('*/*/index');
    }
    
}
