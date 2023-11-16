<?php
declare(strict_types=1);

namespace Sunsgne\WebmanSmsSend\Model;

use Sunsgne\WebmanSmsSend\Traits\OrmCurdTrait;

use support\Model;

/**
 * @Time 2023/11/13 15:01
 * @author sunsgne
 */
class MobileUsers extends Model
{

    use OrmCurdTrait;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mobile_users';

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