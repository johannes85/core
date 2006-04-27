<?php
/* This class is part of the XP framework
 *
 * $Id$
 */
 
  uses('util.profiling.unittest.TestCase');

  /**
   * Tests expression parsing
   *
   * @see      xp://token_get_all
   * @purpose  Unit Test
   */
  class ExpressionTokenizerTest extends TestCase {
    
    /**
     * Retrieve expressions from a given piece of code
     *
     * @access  protected
     * @param   string code
     * @return  string[] expressions
     */
    function expressionsOf($code) {
      $tokens= token_get_all('<?php '.trim($code).' ?>');
      $expressions= array();
      $expression= '';
      $line= 1;
      
      // Iterate over tokens, starting from the T_OPEN_TAG and ending 
      // before the traling T_WHITESPACE and T_CLOSE_TAG tokens.
      for ($i= 1, $s= sizeof($tokens)- 2; $i < $s; $i++) {
        switch ($tokens[$i][0]) {
          case ';':           // EOE
            $expressions[]= array(trim($expression).';', $line);
            $expression= '';
            break;
          
          case T_COMMENT:
          case T_CONSTANT_ENCAPSED_STRING:
          case T_WHITESPACE:
            $line+= substr_count($tokens[$i][1], "\n");
            $expression.= $tokens[$i][1];
            break;
            
          default:
            $expression.= is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i];
        }
      }
      $expression && $expressions[]= array(trim($expression).';', $line);
      
      return $expressions;
    }

    /**
     * Tests empty input will result in an empty array of expressions.
     *
     * @access  public
     */
    #[@test]
    function emptyInput() {
      $this->assertEquals(array(), $this->expressionsOf(''));
    }
    
    /**
     * Tests a single expression
     *
     * @access  public
     */
    #[@test]
    function singleExpression() {
      $this->assertEquals(array(
        array('$a= 1;', 1),
      ), $this->expressionsOf('$a= 1;'));
    }

    /**
     * Tests expression still gets returned even if we have a missing 
     * trailing semicolon (;)
     *
     * @access  public
     */
    #[@test]
    function missingTrailingSemicolon() {
      $this->assertEquals(array(
        array('$a= 1;', 1),
      ), $this->expressionsOf('$a= 1'));
    }

    /**
     * Tests multiple expressions on one line
     *
     * @access  public
     */
    #[@test]
    function multipleExpressionsPerLine() {
      $this->assertEquals(array(
        array('$a= 1;', 1),
        array('$b= 1;', 1),
      ), $this->expressionsOf('$a= 1; $b= 1;'));
    }

    /**
     * Tests an expression spanning multiple lines
     *
     * @access  public
     */
    #[@test]
    function multilineLineExpression() {
      $this->assertEquals(array(
        array('$a= (5 == strlen("Hello")
          ? "good"
          : "bad"
        );', 4),
      ), $this->expressionsOf('
        $a= (5 == strlen("Hello")
          ? "good"
          : "bad"
        );
      '));
    }

    /**
     * Tests two expressions, each on a line by itself
     *
     * @access  public
     */
    #[@test]
    function twoExpressions() {
      $this->assertEquals(array(
        array('statement_on_line_one();', 1),
        array('statement_on_line_two();', 2),
      ), $this->expressionsOf('
        statement_on_line_one(); 
        statement_on_line_two();
      '));
    }

    /**
     * Tests a string containing an expression doesn't get torn apart 
     * into expressions.
     *
     * @access  public
     */
    #[@test]
    function stringsContainingExpressions() {
      $this->assertEquals(array(
        array('echo "A statement: statement_on_line_one();";', 1),
      ), $this->expressionsOf('echo "A statement: statement_on_line_one();";'));
    }
  }
?>
