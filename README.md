# Tags Behavior for Yii2

[![Build Status](https://travis-ci.org/rkit/tags-behavior-yii2.svg?branch=master)](https://travis-ci.org/rkit/tags-behavior-yii2)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/rkit/tags-behavior-yii2/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/rkit/tags-behavior-yii2/?branch=master)

## Requirements

PHP 7

## Installation

```
composer require rkit/tags-behavior-yii2
```

## Configuration

For example, we have a model `Post` and we want to add the ability to specify tags.  
Let's do it.

1. Add a table and a model for the tags

```php
$this->createTable('{{%tag}}', [
    'id' => $this->primaryKey(),
    'name' => $this->string()->notNull()->unique(),
    'frequency' => $this->integer()->notNull()->defaultValue(0),
]);

$this->createTable('{{%post_to_tag}}', [
    'post_id' => $this->integer()->notNull()->defaultValue(0),
    'tag_id' => $this->integer()->notNull()->defaultValue(0),
]);

$this->addPrimaryKey('', '{{%post_to_tag}}', ['post_id', 'tag_id']);
```

2. Add the behavior `TagsBehavior` to the `Post` model

```php
public function behaviors()
{
    return [
        'tagsBehavior' => [
            'class' => 'rkit\tags\behavior\TagsBehavior',
            'relation' => 'tags',
            'tagAttribute' => 'name',
            'tagFrequencyAttribute' => 'frequency', // or false
            'findTag' => function ($value) {
                return Tag::find()->where([$this->tagAttribute => $value])->one();
            },
            'createTag' => function ($value) {
                $tag = new Tag();
                $tag->{$this->tagAttribute} = $value;
                return $tag;
            },
        ],
    ];
}
```

3. Add the relation `tags` (see `relation` option in the behavior)

```php
/**
 * @return \yii\db\ActiveQuery
 */
public function getTags()
{
    return $this
        ->hasMany(Tag::class, ['id' => 'tag_id'])
        ->viaTable('{{%post_to_tag}}', ['post_id' => 'id']);
}
```

## Usage

### Add tags

```php
$model = new Post();
$model->setTagValues(['example1', 'example2']);
$model->save();
```

### Get tags

```php
$post = Post::find()->with('tags')->where(['id' => $id])->one();
$post->getTagValues();
```

### Remove tags

```php
$model = new Post();
$model->setTagValues([]);
$model->save();
```

## Tests

- [See docs](/tests/#tests)

## Coding Standard

- PHP Code Sniffer ([phpcs.xml](./phpcs.xml))
