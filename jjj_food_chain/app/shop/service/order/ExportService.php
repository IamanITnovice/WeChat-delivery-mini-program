<?php

namespace app\shop\service\order;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * 订单导出服务类
 */
class ExportService
{
    /**
     * 订单导出
     */
    public function orderList($list)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        //列宽
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('P')->setWidth(30);

        //设置工作表标题名称
        $sheet->setTitle('订单明细');

        $sheet->setCellValue('A1', '订单号');
        $sheet->setCellValue('B1', '商品信息');
        $sheet->setCellValue('C1', '订单总额');
        $sheet->setCellValue('D1', '包装费');
        $sheet->setCellValue('E1', '实付款金额');
        $sheet->setCellValue('F1', '支付方式');
        $sheet->setCellValue('G1', '下单时间');
        $sheet->setCellValue('H1', '买家');
        $sheet->setCellValue('I1', '买家留言');
        $sheet->setCellValue('J1', '配送方式');
        $sheet->setCellValue('K1', '自提联系电话');
        $sheet->setCellValue('L1', '收货人姓名');
        $sheet->setCellValue('M1', '联系电话');
        $sheet->setCellValue('N1', '收货人地址');
        $sheet->setCellValue('O1', '付款状态');
        $sheet->setCellValue('P1', '付款时间');
        $sheet->setCellValue('Q1', '核销时间');
        $sheet->setCellValue('R1', '订单状态');
        $sheet->setCellValue('S1', '微信支付交易号');
        $sheet->setCellValue('T1', '订单来源');
        $sheet->setCellValue('U1', '退款金额');

        //填充数据
        $index = 0;
        foreach ($list as $order) {
            $address = $order['address'];
            $sheet->setCellValue('A' . ($index + 2), "\t" . $order['order_no'] . "\t");
            $sheet->setCellValue('B' . ($index + 2), $this->filterProductInfo($order));
            $sheet->setCellValue('C' . ($index + 2), $order['order_price']);
            $sheet->setCellValue('D' . ($index + 2), $order['bag_price']);
            $sheet->setCellValue('E' . ($index + 2), $order['pay_price']);
            $sheet->setCellValue('F' . ($index + 2), $order['pay_type']['text']);
            $sheet->setCellValue('G' . ($index + 2), $order['create_time']);
            $sheet->setCellValue('H' . ($index + 2), $order['user'] ? $order['user']['nickName'] : '');
            $sheet->setCellValue('I' . ($index + 2), $order['buy_remark']);
            $sheet->setCellValue('J' . ($index + 2), $order['delivery_type']['text']);
            $sheet->setCellValue('K' . ($index + 2), !empty($order['extract']) ? "\t" . $order['extract']['phone'] . "\t" : '');
            $sheet->setCellValue('L' . ($index + 2), $order['address'] ? $order['address']['name'] : '');
            $sheet->setCellValue('M' . ($index + 2), "\t" . ($order['address'] ? $order['address']['phone'] : '') . "\t");
            $sheet->setCellValue('N' . ($index + 2), $address ? $address->getFullAddress() : '');
            $sheet->setCellValue('O' . ($index + 2), $order['pay_status']['text']);
            $sheet->setCellValue('P' . ($index + 2), $this->filterTime($order['pay_time']));
            $sheet->setCellValue('Q' . ($index + 2), $this->filterTime($order['receipt_time']));
            $sheet->setCellValue('R' . ($index + 2), $order['order_status']['text']);
            $sheet->setCellValue('S' . ($index + 2), $order['transaction_id']);
            $sheet->setCellValue('T' . ($index + 2), $order['order_source_text']);
            $sheet->setCellValue('U' . ($index + 2), $order['refund_money']);
            $index++;
        }

        //保存文件
        $writer = new Xlsx($spreadsheet);
        $filename = '订单-' . date('YmdHis') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . urlencode($filename) . '";filename*=UTF-8\'\'' . urlencode($filename));
        header('Cache-Control: max-age=0');
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit();
    }

    /**
     * 订单导出
     */
    public function deliverList($list)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        //列宽
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(10);

        //设置工作表标题名称
        $sheet->setTitle('订单明细');

        $sheet->setCellValue('A1', '订单号');
        $sheet->setCellValue('B1', '订单金额');
        $sheet->setCellValue('C1', '订单状态');
        $sheet->setCellValue('D1', '配送方式');
        $sheet->setCellValue('E1', '配送费');
        $sheet->setCellValue('F1', '配送状态');
        $sheet->setCellValue('G1', '配送时间');
        $sheet->setCellValue('H1', '送达时间');
        $sheet->setCellValue('I1', '收货人姓名');
        $sheet->setCellValue('J1', '联系电话');
        $sheet->setCellValue('L1', '收货人地址');
        $sheet->setCellValue('L1', '配送员');
        $sheet->setCellValue('M1', '配送员电话');

        //填充数据
        $index = 0;
        foreach ($list as $order) {
            $address = $order['address'];
            $sheet->setCellValue('A' . ($index + 2), "\t" . $order['order_no'] . "\t");
            $sheet->setCellValue('B' . ($index + 2), $order['orders']['order_price']);
            $sheet->setCellValue('C' . ($index + 2), $order['orders']['order_status']['text']);
            $sheet->setCellValue('D' . ($index + 2), $order['deliver_source_text']);
            $sheet->setCellValue('E' . ($index + 2), $order['price']);
            $sheet->setCellValue('F' . ($index + 2), $order['deliver_status_text']);
            $sheet->setCellValue('G' . ($index + 2), $order['create_time']);
            $sheet->setCellValue('H' . ($index + 2), $this->filterTime($order['deliver_time']));
            $sheet->setCellValue('I' . ($index + 2), $order['orders']['address']['name']);
            $sheet->setCellValue('J' . ($index + 2), "\t" . $order['orders']['address']['phone'] . "\t");
            $sheet->setCellValue('K' . ($index + 2), $address ? $address->getFullAddress() : '');
            $sheet->setCellValue('L' . ($index + 2), $order['linkman']);
            $sheet->setCellValue('M' . ($index + 2), $order['phone']);
            $index++;
        }

        //保存文件
        $writer = new Xlsx($spreadsheet);
        $filename = '订单配送信息-' . date('YmdHis') . '.xlsx';

        ob_end_clean();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . urlencode($filename) . '";filename*=UTF-8\'\'' . urlencode($filename));
        header('Cache-Control: max-age=0');
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit();
    }

    /**
     * 格式化商品信息
     */
    private function filterProductInfo($order)
    {
        $content = '';
        foreach ($order['product'] as $key => $product) {
            $content .= ($key + 1) . ".商品名称：{$product['product_name']}\n";
            !empty($product['product_attr']) && $content .= "　商品规格：{$product['product_attr']}\n";
            $content .= "　购买数量：{$product['total_num']}\n";
            $content .= "　商品总价：{$product['total_price']}元\n\n";
        }
        return $content;
    }

    /**
     * 日期值过滤
     */
    private function filterTime($value)
    {
        if (!$value) return '';
        return date('Y-m-d H:i:s', $value);
    }

}