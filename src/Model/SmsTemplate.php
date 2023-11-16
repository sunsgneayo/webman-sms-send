<?php
declare(strict_types=1);
namespace Sunsgne\WebmanSmsRegister\Model;


use Sunsgne\WebmanSmsRegister\Traits\OrmCurdTrait;
use support\Model;

/**
 * @Time 2023/11/13 15:01
 * @author sunsgne
 */
class SmsTemplate extends Model
{

    use OrmCurdTrait;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sms_template';

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