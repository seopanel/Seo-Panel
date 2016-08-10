<?php

/**
 * Unserialize value only if it was serialized.
 *
 * @since 2.0.0
 *
 * @param string $original Maybe unserialized original, if is needed.
 * @return mixed Unserialized data can be any type.
 */
function maybe_unserialize($original) {
    if (is_serialized($original)) // don't attempt to unserialize data that wasn't serialized going in
        return @unserialize($original);
    return $original;
}

/**
 * Check value to find if it was serialized.
 *
 * If $data is not an string, then returned value will always be false.
 * Serialized data is always a string.
 *
 * @since 2.0.5
 *
 * @param string $data   Value to check to see if was serialized.
 * @param bool   $strict Optional. Whether to be strict about the end of the string. Default true.
 * @return bool False if not serialized and true if it was.
 */
function is_serialized($data, $strict = true) {
    // if it isn't a string, it isn't serialized.
    if (!is_string($data)) {
        return false;
    }
    $data = trim($data);
    if ('N;' == $data) {
        return true;
    }
    if (strlen($data) < 4) {
        return false;
    }
    if (':' !== $data[1]) {
        return false;
    }
    if ($strict) {
        $lastc = substr($data, -1);
        if (';' !== $lastc && '}' !== $lastc) {
            return false;
        }
    } else {
        $semicolon = strpos($data, ';');
        $brace = strpos($data, '}');
        // Either ; or } must exist.
        if (false === $semicolon && false === $brace)
            return false;
        // But neither must be in the first X characters.
        if (false !== $semicolon && $semicolon < 3)
            return false;
        if (false !== $brace && $brace < 4)
            return false;
    }
    $token = $data[0];
    switch ($token) {
        case 's' :
            if ($strict) {
                if ('"' !== substr($data, -2, 1)) {
                    return false;
                }
            } elseif (false === strpos($data, '"')) {
                return false;
            }
        // or else fall through
        case 'a' :
        case 'O' :
            return (bool) preg_match("/^{$token}:[0-9]+:/s", $data);
        case 'b' :
        case 'i' :
        case 'd' :
            $end = $strict ? '$' : '';
            return (bool) preg_match("/^{$token}:[0-9.E-]+;$end/", $data);
    }
    return false;
}

/**
 * Check whether serialized data is of string type.
 *
 * @since 2.0.5
 *
 * @param string $data Serialized data.
 * @return bool False if not a serialized string, true if it is.
 */
function is_serialized_string($data) {
    // if it isn't a string, it isn't a serialized string.
    if (!is_string($data)) {
        return false;
    }
    $data = trim($data);
    if (strlen($data) < 4) {
        return false;
    } elseif (':' !== $data[1]) {
        return false;
    } elseif (';' !== substr($data, -1)) {
        return false;
    } elseif ($data[0] !== 's') {
        return false;
    } elseif ('"' !== substr($data, -2, 1)) {
        return false;
    } else {
        return true;
    }
}

/**
 * Serialize data, if needed.
 *
 * @since 2.0.5
 *
 * @param string|array|object $data Data that might be serialized.
 * @return mixed A scalar data
 */
function maybe_serialize($data) {
    if (is_array($data) || is_object($data))
        return serialize($data);

    // Double serialization is required for backward compatibility.
    // See https://core.trac.wordpress.org/ticket/12930
    // Also the world will end. See WP 3.6.1.
    if (is_serialized($data, false))
        return serialize($data);

    return $data;
}

/**
 * Temporarily suspend cache additions.
 *
 * Stops more data being added to the cache, but still allows cache retrieval.
 * This is useful for actions, such as imports, when a lot of data would otherwise
 * be almost uselessly added to the cache.
 *
 * Suspension lasts for a single page load at most. Remember to call this
 * function again if you wish to re-enable cache adds earlier.
 *
 * @since 3.3.0
 *
 * @staticvar bool $_suspend
 *
 * @param bool $suspend Optional. Suspends additions if true, re-enables them if false.
 * @return bool The current suspend setting
 */
function wp_suspend_cache_addition($suspend = null) {
    static $_suspend = false;

    if (is_bool($suspend))
        $_suspend = $suspend;

    return $_suspend;
}

/**
 * Suspend cache invalidation.
 *
 * Turns cache invalidation on and off. Useful during imports where you don't wont to do
 * invalidations every time a post is inserted. Callers must be sure that what they are
 * doing won't lead to an inconsistent cache when invalidation is suspended.
 *
 * @since 2.7.0
 *
 * @global bool $_wp_suspend_cache_invalidation
 *
 * @param bool $suspend Optional. Whether to suspend or enable cache invalidation. Default true.
 * @return bool The current suspend setting.
 */
function wp_suspend_cache_invalidation($suspend = true) {
    global $_wp_suspend_cache_invalidation;

    $current_suspend = $_wp_suspend_cache_invalidation;
    $_wp_suspend_cache_invalidation = $suspend;
    return $current_suspend;
}

/**
 * Convert a value to non-negative integer.
 *
 * @since 2.5.0
 *
 * @param mixed $maybeint Data you wish to have converted to a non-negative integer.
 * @return int A non-negative integer.
 */
function absint($maybeint) {
    return abs(intval($maybeint));
}

/**
 * Mark a function as deprecated and inform when it has been used.
 *
 * There is a hook deprecated_function_run that will be called that can be used
 * to get the backtrace up to what file and function called the deprecated
 * function.
 *
 * The current behavior is to trigger a user error if WP_DEBUG is true.
 *
 * This function is to be used in every function that is deprecated.
 *
 * @since 2.5.0
 * @access private
 *
 * @param string $function    The function that was called.
 * @param string $version     The version of WordPress that deprecated the function.
 * @param string $replacement Optional. The function that should have been called. Default null.
 */
function _deprecated_function($function, $version, $replacement = null) {

    /**
     * Fires when a deprecated function is called.
     *
     * @since 2.5.0
     *
     * @param string $function    The function that was called.
     * @param string $replacement The function that should have been called.
     * @param string $version     The version of WordPress that deprecated the function.
     */
    do_action('deprecated_function_run', $function, $replacement, $version);

    /**
     * Filter whether to trigger an error for deprecated functions.
     *
     * @since 2.5.0
     *
     * @param bool $trigger Whether to trigger the error for deprecated functions. Default true.
     */
}
