<?php

/**
 * @link https://github.com/rkit/tags-behavior-yii2
 * @copyright Copyright (c) 2018 Igor Romanov
 * @license [MIT](http://opensource.org/licenses/MIT)
 */

namespace rkit\tags\behavior;

use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\db\Query;

class TagsBehavior extends Behavior
{
    /**
     * @var string The name of the relation.
     */
    public $relation = 'tags';
    /**
     * @var string The tag attribute name.
     */
    public $tagAttribute = 'name';
    /**
     * @var string|false The tag frequency attribute name.
     */
    public $tagFrequencyAttribute = 'frequency';
    /**
     * @var callable Callback for the function, which should create and return a new tag.
     */
    public $createTag = null;
    /**
     * @var callable Callback for the function, which should find the tag.
     */
    public $findTag = null;
    /**
     * @var string[]
     */
    private $values = null;

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
        ];
    }

    /**
     * Get tags.
     * 
     * @return string[]
     */
    public function getTagValues(): array
    {
        if (!$this->owner->getIsNewRecord() && $this->values === null) {
            foreach ($this->owner->{$this->relation} as $tag) {
                $this->values[] = $tag->{$this->tagAttribute};
            }
        }

        return $this->values === null ? [] : $this->values;
    }

    /**
     * Sets tags.
     * 
     * @param string[] $values
     */
    public function setTagValues($values)
    {
        $this->values = array_unique(array_filter($values));
    }

    /**
     * @inheritdoc
     */
    public function afterSave()
    {
        if ($this->values === null) {
            return;
        }

        if (!$this->owner->getIsNewRecord()) {
            $this->unbindCurrentTags();
        }

        $this->bindTags();
    }

    /**
     * Create a tag.
     * 
     * @param string $value The tag name
     * @return \yii\db\ActiveRecord
     */
    private function createTag($value): \yii\db\ActiveRecord
    {
        $findTag = $this->findTag;
        $tag = $findTag($value);

        if ($tag === null) {
            $createTag = $this->createTag;
            $tag = $createTag($value);
        }

        if ($this->tagFrequencyAttribute !== false) {
            $frequency = $tag->getAttribute($this->tagFrequencyAttribute);
            $tag->setAttribute($this->tagFrequencyAttribute, ++$frequency);
        }

        if ($tag->getIsNewRecord() || $tag->isAttributeChanged($this->tagFrequencyAttribute)) {
            $tag->save();
        }

        return $tag;
    }

    /**
     * Bind tags.
     */
    private function bindTags()
    {
        $relation = $this->owner->getRelation($this->relation);
        $tableRelation = $relation->via->from[0];

        $rows = [];
        foreach ($this->values as $value) {
            $rows[] = [$this->owner->getPrimaryKey(), $this->createTag($value)->getPrimaryKey()];
        }

        if (count($rows)) {
            $this->owner->getDb()
                ->createCommand()
                ->batchInsert($tableRelation, [key($relation->via->link), current($relation->link)], $rows)
                ->execute();
        }
    }

    /**
     * Unbind current tags.
     */
    private function unbindCurrentTags()
    {
        $relation = $this->owner->getRelation($this->relation);
        $tableRelation = $relation->via->from[0];

        if ($this->tagFrequencyAttribute !== false) {
            $class = $relation->modelClass;

            $ids = (new Query())
                ->select(current($relation->link))
                ->from($tableRelation)
                ->where([key($relation->via->link) => $this->owner->getPrimaryKey()])
                ->column($this->owner->getDb());

            if (!empty($ids)) {
                $class::updateAllCounters([$this->tagFrequencyAttribute => -1], ['in', $class::primaryKey(), $ids]);
            }
        }

        $this->owner->getDb()
            ->createCommand()
            ->delete($tableRelation, [key($relation->via->link) => $this->owner->getPrimaryKey()])
            ->execute();
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        $this->unbindCurrentTags();
    }
}
