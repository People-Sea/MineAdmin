<?php

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */

namespace App\Http\Admin\Controller\DataCenter;

use App\Service\DataCenter\DataMaintainService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Mine\Annotation\Auth;
use Mine\Annotation\OperationLog;
use Mine\Annotation\Permission;
use Mine\MineController;
use Psr\Http\Message\ResponseInterface;

/**
 * Class DataMaintainController.
 */
#[Controller(prefix: 'system/dataMaintain'), Auth]
class DataMaintainController extends MineController
{
    #[Inject]
    protected DataMaintainService $service;

    /**
     * 列表.
     */
    #[GetMapping('index'), Permission('system:dataMaintain, system:dataMaintain:index')]
    public function index(): ResponseInterface
    {
        return $this->success($this->service->getPageList($this->request->all()));
    }

    /**
     * 详情.
     */
    #[GetMapping('detailed'), Permission('system:dataMaintain:detailed')]
    public function detailed(): ResponseInterface
    {
        return $this->success($this->service->getColumnList($this->request->input('table', null)));
    }

    /**
     * 优化表.
     */
    #[PostMapping('optimize'), Permission('system:dataMaintain:optimize'), OperationLog]
    public function optimize(): ResponseInterface
    {
        $tables = $this->request->input('tables', []);
        return $this->service->optimize($tables) ? $this->success() : $this->error();
    }

    /**
     * 清理表碎片.
     */
    #[PostMapping('fragment'), Permission('system:dataMaintain:fragment'), OperationLog]
    public function fragment(): ResponseInterface
    {
        $tables = $this->request->input('tables', []);
        return $this->service->fragment($tables) ? $this->success() : $this->error();
    }
}