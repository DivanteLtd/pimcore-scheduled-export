<?php

declare(strict_types=1);

namespace Divante\ScheduledExportBundle\Controller;

use Exception;
use Pimcore\Bundle\AdminBundle\HttpFoundation\JsonResponse;
use Pimcore\Controller\Traits\JsonHelperTrait;
use Pimcore\Controller\UserAwareController;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\GridConfig;
use Pimcore\Model\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class GridConfigController
 *
 * @Route("/admin/scheduled-export/grid-config")
 *
 * @package Divante\ScheduledExportBundle\Controller
 */
class GridConfigController extends UserAwareController
{
    use JsonHelperTrait;

    /** @var string True indicator for share globally */
    private const SHARE_GLOBALLY_TRUE = '1';

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     *
     * @Route("/get-list")
     */
    public function getListAction(Request $request) : JsonResponse
    {
        $user = $this->getPimcoreUser();

        $gridConfigs = new GridConfig\Listing();
        $gridConfigs->setCondition('ownerId = ? or shareGlobally = ?', [$user?->getId(), self::SHARE_GLOBALLY_TRUE]);
        $gridConfigs->load();

        $result = [];

        foreach ($gridConfigs->getGridConfigs() as $gridConfig) {
            $classDefinition = ClassDefinition::getById($gridConfig->getClassId());
            $user = User::getById($gridConfig->getOwnerId());
            if ($user) {
                $userName = $user->getName();
            } else {
                $userName = 'unknown';
            }
            $result[] = [
                'id'   => $gridConfig->getId(),
                'name' => '[' . $gridConfig->getId() . '] ' .
                    ($classDefinition !== null ? $classDefinition->getName() : 'no class found for ClassID: ' . $gridConfig->getClassId()) .
                    ': ' . $gridConfig->getName() . ' (' . $userName . ')'
            ];
        }

        return $this->jsonResponse(['success' => true, 'result' => $result]);
    }
}
