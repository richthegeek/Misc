<?php

# by extending Extender we both allow a shorthand (this->load instead of this->BASE->load) but also
# allow access to the base in the constructor
class Welcome extends Extender
{
    function Welcome()
    {
        # this section does have access to the base, so $this->load etc are all available

    }

    # methods all relate to URIs (or routed URIs?) so this would be /welcome/bob
    function bob()
    {
        //print_r( $this->load );

       // print memory_get_usage();

       print $this->config->get("autoload.test_conf");
    }
}