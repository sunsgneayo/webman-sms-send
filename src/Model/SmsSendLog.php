<?php
declare(strict_types=1);
namespace Sunsgne\WebmanSmsSend\Model;


use support\Db;
use support\Model;

/**
 * @Time 2023/11/13 15:01
 * @author sunsgne
 */
class SmsSendLog extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sms_send_log';


    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;



}
