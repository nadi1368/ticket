<?php

namespace hesabro\ticket\models;

use hesabro\helpers\validators\DateValidator;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * CommentsSearch represents the model behind the search form of `common\models\Comments`.
 */
class CommentsSearch extends Comments
{
    public $fromDate, $toDate;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'creator_id', 'update_id', 'class_id', 'css_class', 'status', 'created', 'changed', 'parent_id', 'unread'], 'integer'],
            [['owner', 'class_name', 'des', 'due_date', 'fromDate', 'toDate'], 'safe'],
            [['fromDate'], DateValidator::class, 'when' => function ($model) {
                return !empty($this->fromDate);
            }],
            [['toDate'], DateValidator::class, 'when' => function ($model) {
                return !empty($this->toDate);
            }],
            ['status', 'default', 'value' => Comments::STATUS_ACTIVE],
            ['status', 'default', 'value' => 1],
        ];
    }

    public function attributeLabels()
    {

        $labels = parent::attributeLabels();
        $labels['fromDate'] = 'تاریخ از';
        $labels['toDate'] = 'تاریخ تا';
        return $labels;
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params, $master = false, $outbox = false, $baseQuery = null, $my = false)
    {
        if ($baseQuery) {
            $query = $baseQuery;
        } else if ($outbox) {
            $query = Comments::find()->outbox();
        } else if ($master) {
            $query = CommentsMaster::find()->inboxMaster();
        } else {
            $query = Comments::find()->inbox();
            (((int) $this->status) === Comments::STATUS_ACTIVE) && $query->excludeViewedThreads();
        }

        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['created' => SORT_DESC]]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'creator_id' => $this->creator_id,
            'update_id' => $this->update_id,
            'class_id' => $this->class_id,
            'css_class' => $this->css_class,
            'status' => $this->status,
            'created' => $this->created,
            'changed' => $this->changed,
        ]);

        $query->andFilterWhere(['like', 'class_name', $this->class_name])
            ->andFilterWhere(['like', 'des', $this->des])
            ->andFilterWhere(['like', 'due_date', $this->due_date]);

        if ($this->unread) {
            if ($master) {
                $query->unreadMaster();
            } else {
                $query->unread();
            }
        }

        if ($this->owner) {
            $query->byOwnerIds(!is_array($this->owner) ? [$this->owner] : $this->owner);
        }

        if($my) {
            $query->my();
        }

        return $dataProvider;
    }

    public function searchApi($params)
    {
        $query = Comments::find()->andWhere(['creator_id' => Yii::$app->user->id]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['due_date' => SORT_ASC]]
        ]);

        $this->load($params, '');

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            //$query->where('0=1');
            return $this;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'class_id' => $this->class_id,
            'css_class' => $this->css_class,
            'status' => $this->status,
            'created' => $this->created,
            'changed' => $this->changed,
        ]);

        $query->andFilterWhere(['like', 'class_name', $this->class_name])
            ->andFilterWhere(['like', 'des', $this->des])
            ->andFilterWhere(['like', 'due_date', $this->due_date]);

        $query->andFilterWhere(['>=', 'due_date', $this->fromDate]);
        $query->andFilterWhere(['<=', 'due_date', $this->toDate]);

        if ($this->owner) {
            $query->byOwnerIds(!is_array($this->owner) ? [$this->owner] : $this->owner);
        }

        return $dataProvider;
    }

    public function searchMyDirectTickets($params = [])
    {
        $query = Comments::find()
            ->isNotSystem()
            ->isParent()
            ->my()
            ->andWhere([
                'kind' => [Comments::KIND_TICKET, Comments::KIND_REFER]
            ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['created' => SORT_DESC]]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'creator_id' => $this->creator_id,
            'class_id' => $this->class_id,
            'css_class' => $this->css_class,
            'status' => $this->status
        ]);

        return $dataProvider;
    }
}
