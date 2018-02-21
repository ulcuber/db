<?php

namespace Db;

use mysqli;

class Store
{
    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    /**
     * Вставляет ассоциативный массив в таблицу
     * @param  string  $table  Название таблицы
     * @param  array   $item   Вставляемый массив
     * @return
     */
    public function insert(string $table, array $item)
    {
        $keys = [];
        $values = [];
        foreach ($item as $key => $value) {
            $keys[] = $this->wrapColumn($key);
            $values[] = $this->wrapValue($value);
        }
        $table = $this->wrapColumn($table);

        $sql = "INSERT INTO " . $table . " (" . implode(", ", $keys) .
            ") VALUES (". implode(", ", $values) . ")";

        return $this->db->query($sql);
    }

    /**
     * Обновляет в таблице запись, соответствующую id в ассоциативном массиве
     * @param  string  $table  Название таблицы
     * @param  array   $item   Обновляемый массив
     * @return
     */
    public function update(string $table, array $item)
    {
        $pairs = [];
        foreach ($item as $key => $value) {
            if ($key != 'id') {
                $pairs[] = $this->wrapColumn($key) . ' = ' . $this->wrapValue($value);
            }
        }
        $table = $this->wrapColumn($table);

        $sql = "UPDATE " . $table . " SET " . implode(", ", $pairs) .
            " WHERE `id` = " . (int) $item['id'];

        return $this->db->query($sql);
    }

    /**
     * Удаляет одну строку таблицы по id
     * @param  string $table
     * @param $id
     * @return
     */
    public function deleteById(string $table, $id)
    {
        $table = $this->wrapColumn($db, $table);
        $sql = "DELETE FROM " . $table . " WHERE id = " . (int) $id . " LIMIT 1";
        return $this->db->query($sql);
    }

    /**
     * Возвращает одну строку таблицы по id
     * @param  string $table
     * @param $id
     * @return
     */
    public function getById(string $table, $id)
    {
        return $this->getByColumn($table, 'id', (int) $id);
    }

    /**
     * Возвращает одну строку таблицы по значению столбца
     * @param  string $table
     * @param  string $column
     * @param $value
     * @return
     */
    public function getByColumn(string $table, string $column, $value)
    {
        $table = $this->wrapColumn($table);
        $column = $this->wrapColumn($column);
        $value = $this->wrapValue($value);
        $sql = "SELECT * FROM {$table} WHERE {$column} = {$value} LIMIT 1";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_assoc() : false;
    }

    /**
     * Возвращает одну строку таблицы
     * @param  string $table
     * @return
     */
    public function getLast(string $table)
    {
        $table = $this->wrapColumn($table);
        $sql = "SELECT * FROM " . $table . " ORDER BY `id` DESC LIMIT 1";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_assoc() : false;
    }

    /**
     * Возвращает последние строки таблицы
     * @param  string $table
     * @param $count
     * @return \Traversable
     */
    public function getLastN(string $table, $count)
    {
        $table = $this->wrapColumn($table);
        $sql = "SELECT * FROM " . $table . " ORDER BY `id` DESC LIMIT " . (int) $count;
        return $this->db->query($sql) ?: [];
    }

    /**
     * Поиск по таблице
     * @param  string $table Имя таблицы
     * @param  array $pairs Пары поиска поле => строка
     * @return
     */
    public function search(string $table, array $pairs)
    {
        $table = $this->wrapColumn($table);
        $sql = "SELECT * FROM " . $table;

        $column = array_keys($pairs)[0];
        $value = array_shift($pairs);
        $column = $this->wrapColumn($column);
        $tokens = $this->prepareSearch($value);

        $token = array_shift($tokens);
        $sql .= " WHERE " . $column . " RLIKE " . $token;

        foreach ($tokens as $token) {
            $sql .= " AND " . $column . " RLIKE " . $token;
        }

        foreach ($pairs as $key => $value) {
            $column = $this->wrapColumn($key);
            $tokens = $this->prepareSearch($value);
            foreach ($tokens as $token) {
                $sql .= " AND " . $column . " RLIKE " . $token;
            }
        }

        return $this->db->query($sql) ?: [];
    }

    /**
     * Разбивает строку поиска на токены
     * @param  string $value Строка поиска
     * @return Array
     */
    private function prepareSearch(string $value)
    {
        if (preg_match("#^[ ]+$#", $value)) {
            return [];
        }
        $search = [',', '.', '\t'];
        $replace = ' ';
        $value = str_replace($search, $replace, $value);
        $value = trim($value);

        $values = explode(' ', $value);
        $tokens = [];
        foreach ($values as $token) {
            $tokens[] = trim($this->wrapValue($token));
        }

        return $tokens;
    }

    /**
     * Экранирует спецсимволы sql и оборачивает строку в обратные кавычки
     * @param  string $column Строка
     * @return [type]        Экранированная строка
     */
    private function wrapColumn(string $column)
    {
        return '`' . $this->db->real_escape_string($column) . '`';
    }

    /**
     * Экранирует спецсимволы sql и оборачивает строку в одинарные кавычки
     * Целочисленное возвращает без изменений
     * @param $value Строка|Число
     * @return string|int
     */
    private function wrapValue($value)
    {
        if (is_int($value)) {
            return $value;
        }
        return "'" . $this->db->real_escape_string($value) . "'";
    }
}
