<?php
if (! function_exists('time_ago')) {
    function time_ago($date, string $timezone = 'UTC')
    {
        $time = \CodeIgniter\I18n\Time::parse($date)->setTimezone($timezone);
        $now  = \CodeIgniter\I18n\Time::now($timezone);

        if ($time->getTimestamp() > $now->getTimestamp()) {
            // If future, force "ago" style
            return $time->difference($now)->humanize();
        }

        return $time->humanize();
    }
}

if (! function_exists('file_status')) {
    /**
     * Return file status array, optionally filtered by value.
     *
     * @param mixed $value Optional. If provided, returns only records with this value.
     * @return array
     */
    function file_status($value = null)
    {
        $status = [
            ["name" => "Draft",  "value" => 0],
            ["name" => "Public", "value" => 1],
            ["name" => "Private","value" => 2],
        ];

        // If no filter, return full array
        if ($value === null) {
            return $status;
        }

        // Filter by value
        return array_values(array_filter($status, function($item) use ($value) {
            return $item['value'] == $value;
        }));
    }
}

if (! function_exists('document_status')) {
    /**
     * Return package status array, or a single record if searched by value/slug.
     *
     * @param mixed $search Optional. Can be an int (value) or string (slug).
     * @return array|mixed
     */
    function document_status($search = null)
    {
        $status = [
            ["name" => "Draft", "value" => 4, "slug" => "draft", "color" => "warning"],
            ["name" => "In Review", "value" => 5, "slug" => "in-review", "color" => "primary"],
            ["name" => "Archived", "value" => 6, "slug" => "archived", "color" => "danger"],
            ["name" => "Complete", "value" => 7, "slug" => "complete", "color" => "success"]
        ];

        // If no filter, return full array
        if ($search === null) {
            return $status;
        }

        // Detect type: search by value (int) or slug (string)
        foreach ($status as $item) {
            if (is_numeric($search) && $item['value'] == $search) {
                return $item; // return first match
            }
            if (is_string($search) && strtolower($item['slug']) === strtolower($search)) {
                return $item; // return first match
            }
        }

        return null; // no match
    }
}

if (! function_exists('getFileType')) {
    /**
     * Return package status array, or a single record if searched by value/slug.
     *
     * @param mixed $search Optional. Can be an int (value) or string (slug).
     * @return array|mixed
     */
    function getFileType($search = null)
    {
        $type = [
            ["name" => "Documents", "value" => 1],
            ["name" => "Files", "value" => 2],
            ["name" => "Images", "value" => 3]
        ];

        // If no filter, return full array
        if ($search === null) {
            return $type;
        }

        // Detect type: search by value (int) or slug (string)
        foreach ($type as $item) {
            if (is_numeric($search) && $item['value'] == $search) {
                return $item; // return first match
            }
        }

        return null; // no match
    }
}

if (! function_exists('getBusinessType')) {
    function getBusinessType()
    {
        $type = [
            ["name" => "Law Office", "value" => "Law Office"],
            ["name" => "IT Professional", "value" => "IT Professional"],
            ["name" => "Something else", "value" => "Other"]
        ];
        return $type;
    }
}
?>