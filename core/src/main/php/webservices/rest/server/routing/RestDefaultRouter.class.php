<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses(
    'webservices.rest.server.routing.RestPath',
    'webservices.rest.server.routing.RestRouter',
    'webservices.rest.server.routing.RestRouting',
    'webservices.rest.server.routing.RestMethodRoute'
  );
  
  /**
   * REST router based on class and method annotations
   *
   * @test    xp://net.xp_framework.unittest.rest.server.RestAnnotationRouterTest
   */
  class RestDefaultRouter extends Object implements RestRouter {
    protected $routes= array();

    /**
     * Parses the "Accept" header
     *
     * @param  string[] accept
     * @param  string pattern suitable for regex
     */
    protected function preferenceOf($accept) {
      $values= array();
      foreach ($accept as $t) {
        if (FALSE === ($p= strpos($t, ';'))) {
          $value= $t;
          $q= 1.0;
        } else {
          $value= substr($t, 0, $p);
          $q= (float)substr($t, $p + 3);    // skip ";q="
        }
        $values[$value]= $q;
      }
      
      asort($values, SORT_NUMERIC);
      return array_reverse($values);
    }

    /**
     * @param   [:float] values
     * @return  string pattern suitable for regex
     */
    protected function patternMatchingAnyOf($values) {
      $pattern= '';
      foreach ($values as $value => $q) {
        $pattern.= ')|('.strtr($value, array('*' => '.+', '#' => '\#', '-' => '\-'));
      }
      return '#('.substr($pattern, 3).')#i';
    }

    /**
     * Configure router
     * 
     * @param  string setup The setup string
     * @param  string base The base URI
     */
    public function configure($setup, $base= '') {
      static $search= '/\{([\w]*)\}/';
      static $replace= '(?P<$1>[%\w:\-\.]*)';

      $package= Package::forName($setup);
      foreach ($package->getClasses() as $handler) {
        if (!$handler->hasAnnotation('webservice')) continue;
        $hbase= $handler->hasAnnotation('webservice', 'path') 
          ? rtrim($handler->getAnnotation('webservice', 'path'), '/')
          : ''
        ;

        foreach ($handler->getMethods() as $method) {
          if (!$method->hasAnnotation('webmethod')) continue;

          $webmethod= $method->getAnnotation('webmethod');
          $pattern= '#^'.$base.$hbase.preg_replace($search, $replace, rtrim($webmethod['path'], '/')).'$#';
          $accept= isset($webmethod['accepts']) ? (array)$webmethod['accepts'] : array('*');
          $this->routes[$webmethod['verb']][$pattern]= array(
            'target'  => $method,
            'accepts' => $this->patternMatchingAnyOf($this->preferenceOf($accept)),
            'returns' => isset($webmethod['returns']) ? $webmethod['returns'] : NULL
          );
        }
      }
    }
    
    /**
     * Get routing table
     * 
     * @return  webservices.rest.server.routing.RestRouting
     */
    public function getRouting() {
      return xp::null();
    }

    /**
     * Return routes for given request and response
     * 
     * @param  scriptlet.http.HttpScriptletRequest request The request
     * @param  scriptlet.http.HttpScriptletResponse response The response
     * @return webservices.rest.server.RestRoute[]
     */
    public function routesFor($request, $response) {
      static $supported= array('application/json', 'text/json', 'text/xml', 'application/xml');

      $verb= $request->getMethod();
      if (!isset($this->routes[$verb])) return FALSE;

      $path= rtrim($request->getURL()->getPath(), '/');
      $preference= $this->preferenceOf(explode(',', $request->getHeader('Accept', '*/*')));
      $accept= $this->patternMatchingAnyOf($preference);
      $mediatype= $request->getHeader('Content-Type', NULL);

      // Figure out matching routes, taking into account what the client
      // tells us it accepts and its content-type
      $matching= array();
      foreach ($this->routes[$verb] as $pattern => $route) {
        if (!preg_match($pattern, $path, $segments)) continue;

        if (NULL === $route['returns']) {
          $returns= NULL;
          foreach ($supported as $type) {
            if (preg_match($accept, $type, $returns)) break;
          }
          if (NULL === $returns) continue;
        } else {
          if (!preg_match($accept, $route['returns'], $returns)) continue;
        }

        if (NULL === $mediatype) {
          $accepts= array(NULL);
        } else {
          if (!preg_match($route['accepts'], $mediatype, $accepts)) continue;
        }

        $matching[]= array(
          'target'   => $route['target'], 
          'segments' => $segments,
          'input'    => $accepts[0],
          'output'   => $returns[0]
        );
      }
      return $matching;
    }
    
    /**
     * Return whether a specified resource exists
     * 
     * @param string resourcePath The resource path
     * @return bool
     */
    public function resourceExists($resourcePath) {
      return FALSE;
    }
  }
?>
