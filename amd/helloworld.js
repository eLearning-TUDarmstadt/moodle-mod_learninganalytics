// Standard license block omitted.
/*
 * @package    block_overview
 * @copyright  2015 Someone cool
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
 /**
  * @module block_overview/helloworld
  */
define(['jquery'], function($) {
 
     /** 
      * Give me blue.
      * @access private
      * @return {string}
      */
     var makeItBlue = function() {
          // We can use our jquery dependency here.
          return $('.blue').show();
     };
 
    /**
     * @constructor
     * @alias module:block_overview/helloworld
     */
    var greeting = function() {
        /** @access private */
        var privateThoughts = 'I like the colour blue';
 
        /** @access public */
        this.publicThoughts = 'I like the colour orange';
 
    };
 
    /**
     * A formal greeting.
     * @access public
     * @return {string}
     */
    greeting.prototype.formal = function() {
        return 'How do you do?';
    };
 
    /**
     * An informal greeting.
     * @access public
     * @return {string}
     */
    greeting.prototype.informal = function() {
        return 'Wassup!';
    };
    return greeting;
});