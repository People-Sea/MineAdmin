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

namespace App\Http\Admin\Controller\Tools;

use App\Http\Admin\Request\GenerateRequest;
use App\Service\Tools\DatasourceService;
use App\Service\Tools\GenerateColumnsService;
use App\Service\Tools\GenerateTablesService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\DeleteMapping;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\PutMapping;
use Mine\Annotation\Auth;
use Mine\Annotation\OperationLog;
use Mine\Annotation\Permission;
use Mine\MineController;
use Psr\Http\Message\ResponseInterface;

/**
 * 代码生成器控制器
 * Class CodeController.
 */
#[Controller(prefix: 'setting/code'), Auth]
class GenerateCodeController extends MineController
{
    /**
     * 信息表服务
     */
    #[Inject]
    protected GenerateTablesService $tableService;

    /**
     * 数据源处理服务
     * DatasourceService.
     */
    #[Inject]
    protected DatasourceService $datasourceService;

    /**
     * 信息字段表服务
     */
    #[Inject]
    protected GenerateColumnsService $columnService;

    /**
     * 代码生成列表分页.
     */
    #[GetMapping('index'), Permission('setting:code')]
    public function index(): ResponseInterface
    {
        return $this->success($this->tableService->getPageList($this->request->All()));
    }

    /**
     * 获取数据源列表.
     */
    #[GetMapping('getDataSourceList'), Permission('setting:code')]
    public function getDataSourceList(): ResponseInterface
    {
        return $this->success($this->datasourceService->getPageList([
            'select' => 'id as value, source_name as label',
        ]));
    }

    /**
     * 获取业务表字段信息.
     */
    #[GetMapping('getTableColumns')]
    public function getTableColumns(): ResponseInterface
    {
        return $this->success($this->columnService->getList($this->request->all()));
    }

    /**
     * 预览代码
     */
    #[GetMapping('preview'), Permission('setting:code:preview')]
    public function preview(): ResponseInterface
    {
        return $this->success($this->tableService->preview((int) $this->request->input('id', 0)));
    }

    /**
     * 读取表数据.
     */
    #[GetMapping('readTable')]
    public function readTable(): ResponseInterface
    {
        return $this->success($this->tableService->read((int) $this->request->input('id')));
    }

    /**
     * 更新业务表信息.
     */
    #[PostMapping('update'), Permission('setting:code:update')]
    public function update(GenerateRequest $request): ResponseInterface
    {
        return $this->tableService->updateTableAndColumns($request->validated()) ? $this->success() : $this->error();
    }

    /**
     * 生成代码
     */
    #[PostMapping('generate'), Permission('setting:code:generate'), OperationLog]
    public function generate(): ResponseInterface
    {
        return $this->_download(
            $this->tableService->generate((array) $this->request->input('ids', [])),
            'mineadmin.zip'
        );
    }

    /**
     * 加载数据表.
     */
    #[PostMapping('loadTable'), Permission('setting:code:loadTable'), OperationLog]
    public function loadTable(GenerateRequest $request): ResponseInterface
    {
        return $this->tableService->loadTable($request->all()) ? $this->success() : $this->error();
    }

    /**
     * 删除代码生成表.
     */
    #[DeleteMapping('delete'), Permission('setting:code:delete'), OperationLog]
    public function delete(): ResponseInterface
    {
        return $this->tableService->delete((array) $this->request->input('ids', [])) ? $this->success() : $this->error();
    }

    /**
     * 同步数据库中的表信息跟字段.
     */
    #[PutMapping('sync/{id}'), Permission('setting:code:sync'), OperationLog]
    public function sync(int $id): ResponseInterface
    {
        return $this->tableService->sync($id) ? $this->success() : $this->error();
    }

    /**
     * 获取所有启用状态模块下的所有模型.
     */
    #[GetMapping('getModels')]
    public function getModels(): ResponseInterface
    {
        return $this->success($this->tableService->getModels());
    }
}