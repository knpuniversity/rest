<?php

namespace KnpU\CodeBattle\Repository;

use KnpU\CodeBattle\Model\Project;

class ProjectRepository extends BaseRepository
{
    protected function getClassName()
    {
        return 'KnpU\CodeBattle\Model\Project';
    }

    protected function getTableName()
    {
        return 'project';
    }

    /**
     * @param $limit
     * @return Project[]
     */
    public function findRandom($limit)
    {
        $stmt = $this->createQueryBuilder('p')
            ->setMaxResults($limit)
            ->execute()
        ;

        $projects = $this->fetchAllToObject($stmt);
        shuffle($projects);

        return array_slice($projects, 0, $limit);
    }
}
