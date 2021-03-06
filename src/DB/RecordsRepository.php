<?php

namespace PHPKitchen\Domain\DB;

use PHPKitchen\Domain;
use PHPKitchen\Domain\Contracts;

/**
 * Represents DB records repository.
 *
 * @package PHPKitchen\Domain\DB
 * @author Dmitry Kolodko <prowwid@gmail.com>
 */
class RecordsRepository extends Base\Repository {
    public function __construct($config = []) {
        $this->entitiesProviderClassName = domain\Data\RecordsProvider::class;
        parent::__construct($config);
    }

    //----------------------- ENTITY MANIPULATION METHODS -----------------------//

    /**
     * @param Record|contracts\DomainEntity $entity
     * @param bool $runValidation
     * @param array $attributes
     * @return bool result.
     * @throws Domain\Exceptions\UnableToSaveEntityException
     */
    protected function saveEntityInternal(contracts\DomainEntity $entity, $runValidation, $attributes) {
        $isEntityNew = $entity->isNew();
        if ($this->triggerModelEvent($isEntityNew ? self::EVENT_BEFORE_ADD : self::EVENT_BEFORE_UPDATE, $entity) && $this->triggerModelEvent(self::EVENT_BEFORE_SAVE, $entity)) {
            $result = $runValidation ? $entity->validateAndSave($attributes) : $entity->saveWithoutValidation($attributes);
        } else {
            $result = false;
        }
        if ($result) {
            $this->triggerModelEvent($isEntityNew ? self::EVENT_BEFORE_ADD : self::EVENT_AFTER_UPDATE, $entity);
            $this->triggerModelEvent(self::EVENT_AFTER_SAVE, $entity);
        } else {
            $exception = new Domain\Exceptions\UnableToSaveEntityException('Failed to save entity ' . get_class($entity));
            $exception->errorsList = $entity->getErrors();
            throw $exception;
        }

        return $result;
    }

    /**
     * @param Record|contracts\DomainEntity $entity
     * @return bool result.
     */
    public function delete(contracts\DomainEntity $entity) {
        if ($this->triggerModelEvent(self::EVENT_BEFORE_DELETE, $entity)) {
            $result = $entity->deleteRecord();
        } else {
            $result = false;
        }
        if ($result) {
            $this->triggerModelEvent(self::EVENT_AFTER_DELETE, $entity);
        }
        return $result;
    }

    /**
     * @param Record|contracts\DomainEntity $entity
     * @return bool result.
     */
    public function validate(contracts\DomainEntity $entity) {
        return $entity->validate();
    }

    //----------------------- INSTANTIATION METHODS -----------------------//

    public function createNewEntity() {
        return $this->container->create([
            'class' => $this->entityClassName,
        ]);
    }

    //----------------------- SEARCH METHODS -----------------------//

    /**
     * @return RecordQuery
     */
    public function find() {
        return $this->createQuery();
    }

    //----------------------- GETTERS/SETTERS -----------------------//

    public function getRecordClassName() {
        return $this->getEntityClassName();
    }
}