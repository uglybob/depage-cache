<?php

namespace Depage\Cache\Providers;

class Redis extends \Depage\Cache\Cache
{
    // {{{ variables
    protected $defaults = array(
        'host' => 'localhost:6379',
    );
    private $redis;
    // }}}
    
    // {{{ constructor
    protected function __construct($prefix, $options = array())
    {
        parent::__construct($prefix, $options);

        $options = array_merge($this->defaults, $options);
        $this->host = $options['host'];

        $this->redis = $this->init();

        if (!is_array($this->host)) {
            $this->host = array($this->host);
        }
        foreach ($this->host as $server) {
            $parts = explode(":", $server);
            $host = $parts[0];
            if (count($parts) == 2) {
                $port = $parts[1];
            } else {
                $port = 6379;
            }

            $this->redis->connect($host, (int) $port);
        }
    }
    // }}}
    // {{{ init
    protected function init()
    {
        return new \Redis();
    }
    // }}}

    // {{{ exist
    /**
     * @brief return if a cache-item with $key exists
     *
     * @return (bool) true if cache for $key exists, false if not
     */
    public function exist($key)
    {
        return $this->redis->exists($key);
    }
    // }}}
    // {{{ age */
    /**
     * @brief returns age of cache-item with key $key
     *
     * @param   $key (string) key of cache item
     *
     * @return (int) age as unix timestamp
     */
    public function age($key)
    {
        // because we don't know the age in memcached we always return false
        return false;
    }
    // }}}
    // {{{ set */
    /**
     * @brief sets data ob a cache item
     *
     * @param   $key  (string) key to save under
     * @param   $data (object) object to save. $data must be serializable
     *
     * @return (bool) true on success, false on failure
     */
    public function set($key, $data)
    {
        $str = serialize($data);

        return $this->redis->set($key, $str);
    }
    // }}}
    // {{{ get */
    /**
     * @brief gets a cached object
     *
     * @param   $key (string) key of item to get
     *
     * @return (object) unserialized content of cache item, false if the cache item does not exist
     */
    public function get($key)
    {
        $value = $this->redis->get($key);

        return unserialize($value);
    }
    // }}}

    // {{{ delete */
    public function delete($key)
    {
        $namespaces = explode("/", $key);
        $last = array_pop($namespaces);

        if ($last != "") {
            // it is just one item - delete directly
            $this->redis->delete($key);
        } else {
            $keys = $this->redis->keys($key . "*");
            $this->redis->delete($keys);
        }
    }
    // }}}
    // {{{ clear */
    /**
     * @brief clears all items from current cache
     *
     * @return void
     */
    public function clear()
    {
        $this->redis->flushAll();
    }
    // }}}
}

/* vim:set ft=php sts=4 fdm=marker et : */