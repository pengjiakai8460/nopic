<?php
/**
 * Created by PhpStorm.

 * Date: 2017/10/13
 * Time: 10:50
 */

namespace common\device;

use common\models\orm\MemberDevice;
use Yii;
use yii\base\Component;

class Device extends Component
{
    private static $deviceID = '';

    final public static function filter()
    {
        $did = Yii::$app->getRequest()->post('_deviceId');

        if ($did) {
            if (!MemberDevice::findOne([
                'device_id' => $did,
                'device_type' => Yii::$app->getRequest()->post('from'),
                'deleted_at' => 0])) {
                return false;
            }

            static::$deviceID = $did;

            return static::getDeviceID();
        }

        return static::registerDevice();
    }

    final public static function getDeviceID()
    {
        return static::$deviceID;
    }

    final public static function registerDevice()
    {
        $device = Yii::$app->getRequest()->post('_device');

        if ($device) {
            $device = json_decode($device, true);

            $memberDevice = MemberDevice::findOne([
                'device_serial_number' => $device['deviceSerialNumber'],
                'device_type' => Yii::$app->getRequest()->post('from'),
                'deleted_at' => 0
            ]);

            if ($memberDevice) {
                static::$deviceID = $memberDevice->device_id;
            }
            else {
                $did = Yii::$app->security->generateRandomString();

                $memberDevice = new MemberDevice();
                $memberDevice->device_serial_number = $device['deviceSerialNumber'];
                $memberDevice->device_name = $device['deviceName'];
                $memberDevice->device_version = $device['deviceVersion'];
                $memberDevice->device_type = Yii::$app->getRequest()->post('from');
                $memberDevice->device_id = $did;

                if ($memberDevice->save()) {
                    static::$deviceID = $did;
                }
            }
        }

        return static::getDeviceID();
    }
}