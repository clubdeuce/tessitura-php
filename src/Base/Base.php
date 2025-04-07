<?php

namespace Clubdeuce\Tessitura\Base;

/**
 * @method mixed extra_args()
 */
class Base {

    /**
     * @var mixed[]
     */
    protected array $_extra_args = [];

    /**
     * Constructor method for initializing the object state.
     *
     * @param string[] $args Optional array of arguments to set the object's state.
     * @return void
     */
    public function __construct( array $args = [] )
    {
        $this->_set_state( $args );
    }

    /**
     * @param  string[] $args
     * @return void
     */
    protected function _set_state( array $args = [] ) : void
    {
        foreach ( $args as $key => $value ) {
            $property = "_{$key}";

            switch ( property_exists ( $this, $property ) ) {
                case true :
                    $this->{$property} = $value;
                    break;
                default :
                    $this->_extra_args[ $key ] = $value;
            }
        }

    }

    /**
     * Magic method to handle dynamic calls to inaccessible or non-existing methods.
     *
     * @param  string $name The name of the method being called.
     * @param  mixed[] $args Optional array of arguments passed to the method call.
     * @return mixed The value of the corresponding property or extra argument if available, or false if neither exists.
     */
    public function __call( string $name, array $args = [] ): mixed
    {
        $property = "_{$name}";

        if ( property_exists ( $this, $property ) ) {
            return $this->{$property};
        }

        if ( isset ( $this->_extra_args[ $name ] ) ) {
            return $this->_extra_args[ $name ];
        }

        return false;
    }

    /**
     * Merges provided arguments with default values.
     *
     * @param  mixed[] $args Array of arguments to override the defaults.
     * @param  mixed[] $defaults Array of default key-value pairs.
     * @return mixed[] The merged array where defaults are overridden by provided arguments.
     */
    public function parse_args( array $args = [], array $defaults = [] ) : array
    {
        foreach ( $defaults as $key => $value ) {
            if ( isset ( $args[ $key ] ) ) {
                continue;
            }

            $args[ $key ] = $value;
        }

        return $args;
    }

}