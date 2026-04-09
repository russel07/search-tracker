<?php

namespace SearchTracker\Rus\Http\Model;

class FilterMeta extends Model
{
    protected $tableName = 'search_tracker_filter_meta';

    public function findByMeta($meta)
    {
        $model = static::getInstance();

        return $model->_wpdb->get_row(
            $model->_wpdb->prepare(
                "SELECT * FROM {$model->tableName} WHERE meta = %s LIMIT 1",
                $meta
            )
        );
    }

    public function setMetaValue($meta, $value)
    {
        $meta = sanitize_text_field($meta);
        $value = is_null($value) ? '' : (string) $value;

        $existing = $this->findByMeta($meta);
        if ($existing && isset($existing->id)) {
            return $this->update(['meta_value' => $value], absint($existing->id));
        }

        return $this->store([
            'meta' => $meta,
            'meta_value' => $value,
        ]);
    }

    public function getMetaValue($meta, $default = '')
    {
        $row = $this->findByMeta(sanitize_text_field($meta));

        if (!$row || !isset($row->meta_value)) {
            return $default;
        }

        return (string) $row->meta_value;
    }
}
