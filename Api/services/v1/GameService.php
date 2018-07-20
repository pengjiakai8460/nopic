<?php


namespace Api\services\v1;

use common\base\BaseService;
use common\models\orm\Game;
use common\models\orm\GameRankEx;
use common\models\orm\GameZone;
use common\models\orm\MemberWealth;
use common\models\orm\RankType;
use common\models\utils\FuncUtil;
use Yii;
use yii\helpers\ArrayHelper;

class GameService extends BaseService
{
    public static $status_zero = 0;
    public static $errors = [
        120001 => '未找到相关配置，请联系管理员！',
    ];

    public static function getAllGame()
    {
        $where = ['deleted_at' => 0];

        $data = Game::find()->select(['id', 'game', 'game_icon', 'mid', 'for_matching', 'for_victorious_promise'])
            ->where($where)
            ->orderBy('sort asc')->all();
        return $data ;
    }

    public static function getGameByWhere($where)
    {
        $query = Game::find()->where(['deleted_at' => 0]);
        if (!empty($query)) {
            $query = $query->andWhere($where);
        }
        $data = $query->asArray()->all();
        return $data ;
    }

    public static function getZoneByWhere($where)
    {
        $query = GameZone::find();
        if (!empty($query)) {
            $query = $query->andWhere($where);
        }
        $data = $query->asArray()->all();
        return $data ;
    }

    public static function getRankByWhere($where)
    {
        $query = GameRankEx::find();
        if (!empty($query)) {
            $query = $query->andWhere($where);
        }
        $data = $query->asArray()->all();
        return $data ;
    }

    public static function getAllrank($gameid)
    {
        $data = RankType::find()->where(['deleted_at' => 0, 'gameid' => $gameid])->all();
        return $data ;
    }

    public static function getGameRank($gameId = 0)
    {
        $query = GameRankEx::find()->select('id,gid,rank,sub_title,order,is_float,male_price,female_price,unit');

        if ($gameId > 0) {
            $query = $query->where(['gid' => $gameId]);
        }

        $data = $query->asArray()->all();

        if (empty($data)) {
            self::error(120001);
        }

        return $data ;
    }

    public static function getGameRankEx($gameId, $rankId = 0)
    {
        $query = GameRankEx::find()->select('id,gid,rank,sub_title,order,is_float,male_price,female_price,unit,commissioner_audit,victorious_promise_price');

        if ($gameId > 0) {
            $query = $query->where(['gid' => $gameId]);
        }

        if ($rankId > 0) {
            $query = $query->andWhere(['id' => $rankId]);
        }

        $ranks = $query->andWhere(['deleted_at' => 0])->asArray()->all();

        if (empty($ranks)) {
            self::error(120001);
        }

        return $ranks;
    }

    public static function getSelectFromRanks($ranks, $gameId)
    {
        $ranks = ArrayHelper::index($ranks, null, 'gid');
        $result = [];

        foreach ($ranks as $gid => $rank) {

            ArrayHelper::multisort($rank, 'order');
            $select = ArrayHelper::index($rank, null, 'sub_title');
            foreach ($select as $subs) {
                if (!empty($subs)) {
                    $result[$gid][] = $subs[0];
                }
            }
        }

        if ($gameId > 0) {
            $result = $result[$gameId];
        } else {
            $result = ['list' => array_values($result)];
        }

        return $result;
    }

    public static function getGameZone($gameId = 0)
    {
        $query = GameZone::find()->select(['id', 'gid', 'zone']);

        if ($gameId > 0) {
            $query = $query->where(['gid' => $gameId, 'deleted_at' => 0]);
        }

        $data = $query->all();

        return $data ;
    }

    public static function getAllGameInfo($for)
    {
        unset($for);

        $games = static::getAllGame();
        $result = [];

        foreach ($games as $game) {
            $game_info = $game->toArray();

            $rank = static::getGameRankEx($game_info['id']);
            $zone = static::getGameZone($game_info['id']);

            $game_info['game_icon'] = FuncUtil::getAbsoluteUri($game_info['game_icon']);
            $game_info['rank'] = $rank;
            $game_info['select'] = static::getSelectFromRanks($rank, $game_info['id']);
            $game_info['zone'] = $zone;

            $result[] = $game_info;
        }

        return $result;
    }

    public static function getGameRankInfo($uid, $beaterId = 0) {
        //$sql = "select r.id,r.rank, r.sub_title, r.gid, r.male_price,r.female_price,g.game,r.unit,r.order,g.mid from game_rank_ex r
        //        left join game g on r.gid=g.id
        //        where g.deleted_at=0 and r.deleted_at=0 group by r.gid,r.rank order by r.order asc";
        $gameRank = (new \yii\db\Query())->select(['r.id','r.rank', 'r.sub_title', 'r.gid', 'r.male_price','r.female_price','g.game','r.unit','r.order','g.mid'])
            ->from("game_rank_ex as r")
            ->where(['g.deleted_at' => self::$status_zero])
            ->andWhere(['r.deleted_at' => self::$status_zero])
            ->leftJoin("game g", "r.gid=g.id")
            ->all();


        //$gameRank = Yii::$app->db->createCommand($sql)->queryAll();
        $title = [];
        foreach ($gameRank as $k => $v) {
            if (!isset($title[$v['gid']])) {
                $title[$v['gid']] = [];
            }
            $data['game'][$v['gid']]['name'] = $v['game'];
            $data['game'][$v['gid']]['id'] = $v['gid'];
            $data['game'][$v['gid']]['mid'] = $v['mid'];
            if (!in_array($v['sub_title'], $title[$v['gid']])) {
                $title[$v['gid']][] = $v['sub_title'];
                $data['rank'][$v['gid']][$k]['name'] = $v['sub_title'];
                $data['rank'][$v['gid']][$k]['id'] = $v['id'];
                $data['rank'][$v['gid']][$k]['male_price'] = $v['male_price'] / 100;
                $data['rank'][$v['gid']][$k]['female_price'] = $v['female_price'] / 100;
                $data['rank'][$v['gid']][$k]['unit'] = $v['unit'];
                $data['rank'][$v['gid']][$k]['order'] = $v['order'];
            }
        }
        $zone = self::getGameZone();
        foreach ($zone as $k => $v) {
            $v['zone'] = trim($v['zone']);
            $zones = explode(' ', $v['zone']);
            if (count($zones) == 3) {
                $data['zone'][$v['gid']][$k]['name'] = $zones[2];
                $data['zone'][$v['gid']][$k]['id'] = $v['id'];
            } else {
                $data['zone'][$v['gid']][$k]['name'] = $v['zone'];
                $data['zone'][$v['gid']][$k]['id'] = $v['id'];
            }
        }
        $ret = [];
        foreach ($data as $k => $v) {
            if ($k == 'game') {
                foreach ($v as $m => $n) {
                    $ret[$k][] = $n;
                }
            }
            if ($k == 'rank' || $k == 'zone') {
                foreach ($v as $m => $n) {
                    foreach ($n as $i => $j)
                    $ret[$k][$m][] = $j;
                }
            }
        }
        //重新组合游戏
        foreach ($ret['game'] as $k => $v) {
            if ($uid && $v['id']) {
                $userSkill = UserSkillService::getUserSkills($uid, $v['id']);
                $ret['game'][$k]['price'] = $userSkill['price'] ?? 0;
            }
            if (isset($data['zone'][$v['id']])) {
                $zone = array_values($data['zone'][$v['id']]);
            } else {
                $zone = [];
            }

            if (isset($data['rank'][$v['id']])) {
                $rank = array_values($data['rank'][$v['id']]);
            } else {
                $rank = [];
            }
            $ret['game'][$k]['zone'] = $zone;
            $ret['game'][$k]['rank'] = $rank;

        }
        unset($ret['zone']);
        unset($ret['rank']);

        //性别
        $ret['sex'][0]['name'] = '无要求';
        $ret['sex'][0]['id'] = 0;
        $ret['sex'][1]['name'] = '男';
        $ret['sex'][1]['id'] = 1;
        $ret['sex'][2]['name'] = '女';
        $ret['sex'][2]['id'] = 2;
        $ret['remarks_num'] = 50;

        //判断是否是私人单如果是私人单,则需要获取打手信息
        if ($beaterId) {
            $ret['beater'] = UserCacheService::getUserInfo($beaterId, ['id', 'avator', 'nickname', 'sex', 'age']);
        }

        //获取用户余额
        $member = MemberWealth::find()->where(['uid' => $uid])->asArray()->one();
        $ret['money'] = $member['money'] ?? 0;

        return $ret;

    }


}