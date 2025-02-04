<?php

class Users extends Api_configuration
{

    public function create(
        string $name,
        string $email,
        string $password,
        string $position
    ) {

        $values = '
        "' . $name . '",
        "' . $email . '",
        "' . password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]) . '",
        "' . $position . '"
        ';

        $sql = 'INSERT INTO `users` (`name`, `email`, `password`,`position`) VALUES (' . $values . ')';
        $create_user = $this->db_create($sql);
        if ($create_user) {
            $slug = $this->slugify($create_user . '-' . $name);
            $sql = 'UPDATE `users` SET `slug` = "' . $slug . '" WHERE `id` = ' . $create_user;
            $this->db_update($sql);

            return [
                'id' => (int) $create_user,
                'name' => $name,
                'email' => $email,
                'position' => $position,
                'slug' => $slug
            ];
        } else {
            http_response_code(400);
            return ['message' => "Error creating user"];
        }
    }

    public function read()
    {
        $sql = 'SELECT `id`, `name`, `email`,`position`, `slug` FROM `users`';
        $get_users = $this->db_read($sql);
        if ($this->db_num_rows($get_users) > 0) {
            $users = [];
            while ($users_object = $this->db_object($get_users)) {
                $users[] = [
                    'id' => (int) $users_object->id,
                    'name' => $users_object->name,
                    'email' => $users_object->email,
                    'position' => $users_object->position,
                    'slug' => $users_object->slug
                ];
            }
            return $users;
        } else {
            return [];
        }
    }

    public function read_by_slug(
        string $slug
    ) {
        $sql = 'SELECT `id`, `name`, `email`, `position`, `slug` FROM `users` WHERE `slug` = "' . $slug . '"';
        $get_users = $this->db_read($sql);
        if ($this->db_num_rows($get_users) > 0) {
            $users = $this->db_object($get_users);
            $users->id = (int) $users->id;
            return $users;
        } else {
            return [];
        }
    }

    private function read_by_id(
        int $id
    ) {
        $sql = 'SELECT `id`, `name`, `email`, `position`, `slug` FROM `users` WHERE `id` = "' . $id . '"';
        $get_users = $this->db_read($sql);
        if ($this->db_num_rows($get_users) > 0) {
            $users = $this->db_object($get_users);
            $users->id = (int) $users->id;
            return $users;
        } else {
            return [];
        }
    }

    public function update(
        int $id,
        string $name,
        string $email,
        string $position
    ) {
        $old_user = $this->read_by_id($id);
        if ($old_user) {
            $sql = 'UPDATE `users` SET `name` = "' . $name . '" , `email` = "' . $email . '" , `position` = "' . $position . '" , `slug` = "' . $this->slugify($id . '-' . $name) . '"  WHERE `id` = "' . $id .  '"';
            if ($this->db_update($sql)) {
                return [
                    'old_user' => $old_user,
                    'new_user' => [
                        'id' => $id,
                        'name' => $name,
                        'email' => $email,
                        'position' => $position,
                        'slug' => $this->slugify($id . '-' . $name)
                    ]
                ];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function delete(
        string $slug
    ) {
        $old_user = $this->read_by_slug($slug);
        if ($old_user) {
            $sql = 'DELETE FROM `users` WHERE `slug` = "' . $slug . '"';
            if ($this->db_delete($sql)) {
                return [
                    'old_user' => $old_user
                ];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
