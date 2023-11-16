<?php
declare(strict_types=1);
namespace Sunsgne\WebmanSmsSend\Model;


use support\Model;

/**
 * @Time 2023/11/13 15:01
 * @author sunsgne
 */
class CountryMobile extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'country_mobile';

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