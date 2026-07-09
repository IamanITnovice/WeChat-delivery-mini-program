<?php

namespace app\common\service\qrcode;

use app\common\model\store\Table as TableModel;

/**
 * 二维码
 */
class TableService extends Base
{
    private $id;
    private $source;

    /**
     * 构造方法
     */
    public function __construct($id, $source)
    {
        parent::__construct();
        $this->id = $id;
        $this->source = $source;
    }

    /**
     * 获取小程序码
     */
    public function getImage()
    {
        $table = TableModel::detail($this->id);
        if ($this->source == 'wx') {
            // 下载小程序码
            return $this->saveQrcodeToDir($table['app_id'], 'pages/product/share-login', $this->id, $table['shop_supplier_id'], $this->source);
        } else if ($this->source == 'mp' || $this->source == 'h5') {
            return $this->saveMpQrcodeToDir('h5/pages/product/share-login', $this->id, $table['app_id'], $table['shop_supplier_id'], $this->source);
        }
    }

}