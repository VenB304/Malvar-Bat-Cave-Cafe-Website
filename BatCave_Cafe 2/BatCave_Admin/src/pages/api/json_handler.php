<?php

class JsonHandler
{
    private $filepath;

    public function __construct($filepath)
    {
        $this->filepath = $filepath;
        if (!file_exists($this->filepath)) {
            file_put_contents($this->filepath, json_encode([]));
        }
    }

    public function read()
    {
        if (!file_exists($this->filepath)) {
            return [];
        }
        $content = file_get_contents($this->filepath);
        return json_decode($content, true) ?? [];
    }

    public function write($data)
    {
        return file_put_contents($this->filepath, json_encode($data, JSON_PRETTY_PRINT));
    }

    public function append($item)
    {
        $data = $this->read();
        // Generate a simple ID if not present
        if (!isset($item['id'])) {
            $item['id'] = uniqid();
        }
        $data[] = $item;
        $this->write($data);
        return $item['id'];
    }

    public function update($id, $newData)
    {
        $data = $this->read();
        $updated = false;
        foreach ($data as $key => $item) {
            if (isset($item['id']) && $item['id'] == $id) {
                $data[$key] = array_merge($item, $newData);
                $updated = true;
                break;
            }
        }
        if ($updated) {
            $this->write($data);
        }
        return $updated;
    }

    public function delete($id)
    {
        $data = $this->read();
        $newData = [];
        $deleted = false;
        foreach ($data as $item) {
            if (isset($item['id']) && $item['id'] == $id) {
                $deleted = true;
                continue;
            }
            $newData[] = $item;
        }
        if ($deleted) {
            $this->write($newData);
        }
        return $deleted;
    }
}
