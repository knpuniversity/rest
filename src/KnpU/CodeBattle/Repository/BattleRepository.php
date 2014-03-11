<?php

namespace KnpU\CodeBattle\Repository;

use Doctrine\DBAL\Driver\ResultStatement;
use KnpU\CodeBattle\Model\Battle;

class BattleRepository extends BaseRepository
{
    protected function getClassName()
    {
        return 'KnpU\CodeBattle\Model\Battle';
    }

    protected function getTableName()
    {
        return 'battle';
    }

    protected function finishHydrateObject($obj)
    {
        /** @var Battle $obj */
        // normalize the date back to an object
        $obj->foughtAt = \DateTime::createFromFormat('Y-m-d H:i:s', $obj->foughtAt);
    }

}
