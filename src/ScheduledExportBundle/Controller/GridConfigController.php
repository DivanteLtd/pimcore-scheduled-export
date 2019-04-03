<?php

namespace Divante\ScheduledExportBundle\Controller;

use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use Pimcore\Bundle\AdminBundle\Security\User\User;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\GridConfig;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class GridConfigController
 *
 * @package Divante\ScheduledExportBundle\Controller
 */
class GridConfigController extends AdminController
{
    /**
     * @param Request $request
     * @return \Pimcore\Bundle\AdminBundle\HttpFoundation\JsonResponse
     * @Route("/admin/scheduled-export/grid-config/get-list")
     */
    public function getListAction(Request $request)
    {
        $gridConfigs = new GridConfig\Listing();
        $gridConfigs->load();
        $result = [];

        /** @var GridConfig $gridConfig */
        foreach ($gridConfigs->gridConfigs as $gridConfig) {
            $classDefinition = ClassDefinition::getById($gridConfig->getClassId());
            $user = \Pimcore\Model\User::getById($gridConfig->getOwnerId());
            $result[] = [
                "id"    => $gridConfig->getId(),
                "name"  => $classDefinition->getName().":".$gridConfig->getName()." (".$user->getName().")"
            ];
        }

        return $this->adminJson(["success" => true, "result" => $result]);
    }
}
