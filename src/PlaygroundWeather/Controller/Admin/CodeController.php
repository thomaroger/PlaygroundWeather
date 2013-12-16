<?php
namespace PlaygroundWeather\Controller\Admin;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use PlaygroundWeather\Service\Code as CodeService;

class CodeController extends AbstractActionController
{
    /**
     * @var CodeService
     */
    protected $codeService;

    public function addAction()
    {
        $form = $this->getServiceLocator()->get('playgroundweather_code_form');
        $form->get('submit')->setLabel("Créer");
        $form->get('codes')->setCount(1)->prepareFieldset();
        $form->setAttribute('action', '');
        if ($this->getRequest()->isPost()) {
            $data = array_replace_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );
            if(empty($data['codes'])){
                $data['codes'] = array();
            }
            $form->setData($data);
            if ($form->isValid()) {
                $data = $form->getData();
                $code = $this->getCodeService()->create(current($data['codes']));
                if ($code) {
                    return $this->redirect()->toRoute('admin/weather/codes/list');
                }
            } else {
                foreach ($form->get('codes')->getMessages() as $errMsg) {
                    foreach ($errMsg as $field => $msg) {
                        $this->flashMessenger()->addMessage($field . ' - ' . current($msg));
                    }
                }
                return $this->redirect()->toRoute('admin/weather/codes/add');
            }
        }
        // Display
        return new ViewModel(
            array(
                'form' => $form,
                'flashMessages' => $this->flashMessenger()->getMessages(),
            )
        );
    }

    public function editAction()
    {
        $codeId = $this->getEvent()->getRouteMatch()->getParam('codeId');
        if (!$codeId) {
            return $this->redirect()->toRoute('admin/weather/codes/list');
        }
        $codeMapper = $this->getCodeService()->getCodeMapper();
        $code = $codeMapper->findById($codeId);
        if (!$code) {
            return $this->redirect()->toRoute('admin/weather/codes/list');
        }

        $form = $this->getServiceLocator()->get('playgroundweather_code_form');
        $form->get('submit')->setLabel("Modifier");
        $form->get('codes')->setCount(1)->prepareFieldset();
        $form->setAttribute('action', '');

        $data = array();
        $data['codes'][0] = $code->getArrayCopy();
        $form->setData($data);

        if ($this->getRequest()->isPost()) {
            $data = array_replace_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );
            if(empty($data['codes'])){
                $data['codes'] = array();
            }
            $form->setData($data);
            if ($form->isValid()) {
                $data = $form->getData();
                $code = $this->getCodeService()->edit($code->getId(), current($data['codes']));
                if ($code) {
                    return $this->redirect()->toRoute('admin/weather/codes/list');
                }
            } else {
                foreach ($form->get('codes')->getMessages() as $errMsg) {
                    foreach ($errMsg as $field => $msg) {
                        $this->flashMessenger()->addMessage($field . ' - ' . current($msg));
                    }
                }
                return $this->redirect()->toRoute('admin/weather/codes/edit');
            }
        }

        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-weather/code/add');
        $viewModel->setVariables(
            array(
                'form' => $form,
                'flashMessages' => $this->flashMessenger()->getMessages(),
            )
        );
        return $viewModel;
    }

    public function removeAction()
    {
        $codeId = $this->getEvent()->getRouteMatch()->getParam('codeId');
        if (!$codeId) {
            return $this->redirect()->toRoute('admin/weather/codes/list');
        }
        $result = $this->getCodeService()->remove($codeId);
        if (!$result) {
            $this->flashMessenger()->addMessage('Une erreur est survenue pendant la suppression de l\'état du ciel personnalisé');
        } else {
            $this->flashMessenger()->addMessage('L\'état du ciel personnalisé a bien été supprimé');
        }
        return $this->redirect()->toRoute('admin/weather/codes/list');
    }

    public function importAction()
    {
        $form = $this->getServiceLocator()->get('playgroundweather_fileimport_form');
        $form->get('submit')->setLabel("Importer");

        if ($this->getRequest()->isPost()) {
            $data = array_replace_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );
            $form->setData($data);
            // Create and set file validator
            $inputFilter = new \Zend\InputFilter\InputFilter();
            $fileFilter = new \Zend\InputFilter\FileInput('file');
            $validatorChain = new \Zend\Validator\ValidatorChain();
            $validatorChain->attach(new \Zend\Validator\File\Exists());
            $validatorChain->attach(new \Zend\Validator\File\Extension('xml'));
            $fileFilter->setValidatorChain($validatorChain);
            $fileFilter->setRequired(true);
            $inputFilter->add($fileFilter);
            $form->setInputFilter($inputFilter);
            if ($form->isValid()) {
                $result = $this->getCodeService()->import($data['file']);
                if ($result) {
                    return $this->redirect()->toRoute('admin/weather/codes/list');
                } else {
                    $this->flashMessenger()->addMessage('les données du fichier n\'ont pas pu être importées');
                }
            } else {
                foreach ($form->getMessages() as $field => $errMsg) {
                    $this->flashMessenger()->addMessage($field . ' - ' . current($errMsg));
                }
            }
            return $this->redirect()->toRoute('admin/weather/codes/import');
        }
        return new ViewModel(
            array(
                'form' => $form,
                'flashMessages' => $this->flashMessenger()->getMessages(),
            )
        );
    }

    public function associateAction()
    {
        $codeMapper = $this->getCodeService()->getCodeMapper();

        $form = $this->getServiceLocator()->get('playgroundweather_code_form');
        $form->get('submit')->setLabel("Enregistrer");

        $appCodes = $codeMapper->findBy(array('isDefault' => 0));
        $providerCodes = $codeMapper->findBy(array('isDefault' => 1));

        $form->get('codes')->setCount(count($providerCodes))->prepareFieldset();
        $data = array();
        $data['codes'] = array();
        foreach ($providerCodes as $code) {
            $data['codes'][] = $code->getArrayCopy();
        }
        $form->setData($data);

        if ($this->getRequest()->isPost()) {
            $data = array_replace_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );
            if(empty($data['codes'])){
                $data['codes'] = array();
            }
            $form->setData($data);
            if ($form->isValid()) {
                $data = $form->getData();
                foreach ($data['codes'] as $codeData) {
                    $this->getCodeService()->edit($codeData['id'], $codeData);
                }
            } else {
                foreach ($form->get('codes')->getMessages() as $errMsg) {
                    foreach ($errMsg as $field => $msg) {
                        $this->flashMessenger()->addMessage($field . ' - ' . current($msg));
                    }
                }
            }
            return $this->redirect()->toRoute('admin/weather/codes/list');
        }

        return new ViewModel(
            array(
                'appCodes' => $appCodes,
                'providerCodes' =>  $providerCodes,
                'form' => $form,
                'flashMessages' => $this->flashMessenger()->getMessages(),
            )
        );
    }

    public function getCodeService()
    {
        if ($this->codeService === null) {
            $this->codeService = $this->getServiceLocator()->get('playgroundweather_code_service');
        }
        return $this->codeService;
    }

    public function setCodeService($codeService)
    {
        $this->codeService = $codeService;

        return $this;
    }

}