<?php

namespace Clubdeuce\Tessitura\Base;

/**
 * @method array extra_args()
 */
class Base {

    protected array $_extra_args = [];

    public function __construct( array $args = [] ) {

        $this->_set_state( $args );

    }

    protected function _set_state( array $args = [] ) : void {

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

    public function __call( string $name, array $args = [] ) {

        $property = "_{$name}";

        if ( property_exists ( $this, $property ) ) {
            return $this->{$property};
        }

        if ( isset ( $this->_extra_args[ $name ] ) ) {
            return $this->_extra_args[ $name ];
        }

        return false;

    }

}