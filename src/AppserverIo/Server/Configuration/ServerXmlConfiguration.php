<?php

/**
 * \AppserverIo\Server\Configuration\ServerXmlConfiguration
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @author    Johann Zelger <jz@appserver.io>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/server
 * @link      http://www.appserver.io
 */

namespace AppserverIo\Server\Configuration;

use AppserverIo\Server\Interfaces\ServerConfigurationInterface;

/**
 * Class ServerXmlConfiguration
 *
 * @author    Johann Zelger <jz@appserver.io>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/server
 * @link      http://www.appserver.io
 */
class ServerXmlConfiguration implements ServerConfigurationInterface
{
    /**
     * The configured rewrite rules
     *
     * @var array
     */
    protected $rewrites;

    /**
     * The configured locations.
     *
     * @var array
     */
    protected $locations;
    
    /**
     * The configured headers
     *
     * @var array
     */
    protected $headers;

    /**
     * Holds the environmentVariables array
     *
     * @var array
     */
    protected $environmentVariables = array();

    /**
     * Constructs config
     *
     * @param \SimpleXMLElement $node The simple xml element used to build config
     */
    public function __construct($node)
    {
        // prepare properties
        $this->name = (string)$node->attributes()->name;
        $this->type = (string)$node->attributes()->type;
        $this->workerType = (string)$node->attributes()->worker;
        $this->socketType = (string)$node->attributes()->socket;
        $this->streamContextType = (string)$node->attributes()->streamContext;
        $this->serverContextType = (string)$node->attributes()->serverContext;
        $this->requestContextType = (string)$node->attributes()->requestContext;
        $this->loggerName = (string)$node->attributes()->loggerName;
        $this->transport = (string)array_shift($node->xpath("./params/param[@name='transport']"));
        $this->address = (string)array_shift($node->xpath("./params/param[@name='address']"));
        $this->port = (int)array_shift($node->xpath("./params/param[@name='port']"));
        $this->software = (string)array_shift($node->xpath("./params/param[@name='software']"));
        $this->workerNumber = (int)array_shift($node->xpath("./params/param[@name='workerNumber']"));
        $this->workerAcceptMin = (int)array_shift($node->xpath("./params/param[@name='workerAcceptMin']"));
        $this->workerAcceptMax = (int)array_shift($node->xpath("./params/param[@name='workerAcceptMax']"));
        $this->certPath = (string)array_shift($node->xpath("./params/param[@name='certPath']"));
        $this->passphrase = (string)array_shift($node->xpath("./params/param[@name='passphrase']"));
        $this->documentRoot = (string)array_shift($node->xpath("./params/param[@name='documentRoot']"));
        $this->directoryIndex = (string)array_shift($node->xpath("./params/param[@name='directoryIndex']"));
        $this->admin = (string)array_shift($node->xpath("./params/param[@name='admin']"));
        $this->keepAliveMax = (string)array_shift($node->xpath("./params/param[@name='keepAliveMax']"));
        $this->keepAliveTimeout = (string)array_shift($node->xpath("./params/param[@name='keepAliveTimeout']"));
        $this->autoIndex = (boolean)array_shift($node->xpath("./params/param[@name='autoIndex']"));
        $this->errorsPageTemplatePath = (string)array_shift($node->xpath("./params/param[@name='errorsPageTemplatePath']"));
        $this->welcomePageTemplatePath = (string)array_shift($node->xpath("./params/param[@name='welcomePageTemplatePath']"));
        $this->autoIndexTemplatePath = (string)array_shift($node->xpath("./params/param[@name='autoIndexTemplatePath']"));

        // prepare analytics
        $this->analytics = $this->prepareAnalytics($node);
        // prepare modules
        $this->headers = $this->prepareHeaders($node);
        // prepare modules
        $this->modules = $this->prepareModules($node);
        // prepare connection handlers
        $this->connectionHandlers = $this->prepareConnectionHandlers($node);
        // prepare handlers
        $this->handlers = $this->prepareHandlers($node);
        // prepare virutalHosts
        $this->virtualHosts = $this->prepareVirtualHosts($node);
        // prepare rewrites
        $this->rewrites = $this->prepareRewrites($node);
        // prepare environmentVariables
        $this->environmentVariables = $this->prepareEnvironmentVariables($node);
        // prepare authentications
        $this->authentications = $this->prepareAuthentications($node);
        // prepare accesses
        $this->accesses = $this->prepareAccesses($node);
        // prepare locations
        $this->locations = $this->prepareLocations($node);
        // prepare rewrite maps
        $this->rewriteMaps = $this->prepareRewriteMaps($node);
        // prepare certificates
        $this->certificates = $this->prepareCertificates($node);
    }
    
    /**
     * Prepares the headers array based on a simple xml element node
     *
     * @param \SimpleXMLElement $node The xml node
     *
     * @return array
     */
    public function prepareHeaders(\SimpleXMLElement $node)
    {
        $headers = array();
        if ($node->headers) {
            foreach ($node->headers->header as $headerNode) {
                // Cut of the SimpleXML attributes wrapper and attach it to our headers
                $override = false;
                $overrideAttribute = strtolower((string)$headerNode->attributes()->override);
                if ($overrideAttribute && $overrideAttribute === 'true') {
                    $override = true;
                }
                $append = false;
                $appendAttribute = strtolower((string)$headerNode->attributes()->append);
                if ($appendAttribute && $appendAttribute === 'true') {
                    $append = true;
                }
                $header = array(
                    'type' => (string) $headerNode->attributes()->type,
                    'name' => (string) $headerNode->attributes()->name,
                    'value' => (string) $headerNode->attributes()->value,
                    'uri' => (string) $headerNode->attributes()->uri,
                    'override' => $override,
                    'append' => $append
                );
                $headers[(string) $headerNode->attributes()->type][] = $header;
            }
        }
        return $headers;
    }

    /**
     * Prepares the modules array based on a simple xml element node
     *
     * @param \SimpleXMLElement $node The xml node
     *
     * @return array
     */
    public function prepareModules(\SimpleXMLElement $node)
    {
        $modules = array();
        if ($node->modules) {
            foreach ($node->modules->module as $moduleNode) {
                $modules[] = (string)$moduleNode->attributes()->type;
            }
        }
        return $modules;
    }

    /**
     * Prepares the connectionHandlers array based on a simple xml element node
     *
     * @param \SimpleXMLElement $node The xml node
     *
     * @return array
     */
    public function prepareConnectionHandlers(\SimpleXMLElement $node)
    {
        $connectionHandlers = array();
        if ($node->connectionHandlers) {
            foreach ($node->connectionHandlers->connectionHandler as $connectionHandlerNode) {
                $connectionHandlerType = (string)$connectionHandlerNode->attributes()->type;
                $connectionHandlers[] = $connectionHandlerType;
            }
        }
        return $connectionHandlers;
    }

    /**
     * Prepares the handlers array based on a simple xml element node
     *
     * @param \SimpleXMLElement $node The xml node
     *
     * @return array
     */
    public function prepareHandlers(\SimpleXMLElement $node)
    {
        $handlers = array();
        if ($node->handlers) {
            foreach ($node->handlers->handler as $handlerNode) {
                $params = array();
                if ($handlerNode->params->param) {
                    foreach ($handlerNode->params->param as $paramNode) {
                        $paramName = (string)$paramNode->attributes()->name;
                        $params[$paramName] = (string)array_shift($handlerNode->xpath(".//param[@name='$paramName']"));
                    }
                }
                $handlers[(string)$handlerNode->attributes()->extension] = array(
                    'name' => (string)$handlerNode->attributes()->name,
                    'params' => $params
                );
            }
        }
        return $handlers;
    }

    /**
     * Prepares the virtual hosts array based on a simple xml element node
     *
     * @param \SimpleXMLElement $node The xml node
     *
     * @return array
     */
    public function prepareVirtualHosts(\SimpleXMLElement $node)
    {
        $virutalHosts = array();
        if ($node->virtualHosts) {
            foreach ($node->virtualHosts->virtualHost as $virtualHostNode) {
                $virtualHostNames = explode(' ', (string)$virtualHostNode->attributes()->name);
                $params = array();
                foreach ($virtualHostNode->params->param as $paramNode) {
                    $paramName = (string)$paramNode->attributes()->name;
                    $params[$paramName] = (string)array_shift($virtualHostNode->xpath(".//param[@name='$paramName']"));
                }
                foreach ($virtualHostNames as $virtualHostName) {
                    // set all virtual hosts params per key for faster matching later on
                    $virutalHosts[trim($virtualHostName)] = array(
                        'params' => $params,
                        'headers' => $this->prepareHeaders($virtualHostNode),
                        'rewriteMaps' => $this->prepareRewriteMaps($virtualHostNode),
                        'rewrites' => $this->prepareRewrites($virtualHostNode),
                        'locations' => $this->prepareLocations($virtualHostNode),
                        'environmentVariables' => $this->prepareEnvironmentVariables($virtualHostNode),
                        'authentications' => $this->prepareAuthentications($virtualHostNode),
                        'accesses' => $this->prepareAccesses($virtualHostNode),
                        'analytics' => $this->prepareAnalytics($virtualHostNode)
                    );
                }
            }
        }
        return $virutalHosts;
    }

    /**
     * Prepares the rewrite maps based on a simple xml element node
     *
     * @param \SimpleXMLElement $node The xml node
     *
     * @return array
     */
    public function prepareRewriteMaps(\SimpleXMLElement $node)
    {
        $rewriteMaps = array();
        if ($node->rewriteMaps) {
            foreach ($node->rewriteMaps->rewriteMap as $rewriteMapNode) {
                $rewriteMapType = (string)$rewriteMapNode->attributes()->type;
                $params = array();
                foreach ($rewriteMapNode->params->param as $paramNode) {
                    $paramName = (string)$paramNode->attributes()->name;
                    $params[$paramName] = (string)array_shift($rewriteMapNode->xpath(".//param[@name='$paramName']"));
                }
                $rewriteMaps[$rewriteMapType] = $params;
            }
        }
        return $rewriteMaps;
    }

    /**
     * Prepares the rewrites array based on a simple xml element node
     *
     * @param \SimpleXMLElement $node The xml node
     *
     * @return array
     */
    public function prepareRewrites(\SimpleXMLElement $node)
    {
        $rewrites = array();
        if ($node->rewrites) {
            foreach ($node->rewrites->rewrite as $rewriteNode) {
                // Cut of the SimpleXML attributes wrapper and attach it to our rewrites
                $rewrite = (array)$rewriteNode;
                $rewrites[] = array_shift($rewrite);
            }
        }
        return $rewrites;
    }
    
    /**
     * Prepares the certificates array based on a simple xml element node
     *
     * @param \SimpleXMLElement $node The xml node
     *
     * @return array
     */
    public function prepareCertificates(\SimpleXMLElement $node)
    {
        $certificates = array();
        if ($node->certificates) {
            foreach ($node->certificates->certificate as $certificateNode) {
                // Cut of the SimpleXML attributes wrapper and attach it to our locations
                $certificate = array(
                    'domain' => (string) $certificateNode->attributes()->domain,
                    'certPath' => (string) $certificateNode->attributes()->certPath
                );
                $certificates[] = $certificate;
            }
        }
        return $certificates;
    }

    /**
     * Prepares the locations array based on a simple xml element node
     *
     * @param \SimpleXMLElement $node The xml node
     *
     * @return array
     */
    public function prepareLocations(\SimpleXMLElement $node)
    {
        $locations = array();
        if ($node->locations) {
            foreach ($node->locations->location as $locationNode) {
                // Cut of the SimpleXML attributes wrapper and attach it to our locations
                $location = array(
                    'condition' => (string) $locationNode->attributes()->condition,
                    'handlers' => $this->prepareHandlers($locationNode),
                    'headers' => $this->prepareHeaders($locationNode),
                );
                $locations[] = $location;
            }
        }
        return $locations;
    }

    /**
     * Prepares the environmentVariables array based on a simple xml element node
     *
     * @param \SimpleXMLElement $node The xml node
     *
     * @return array
     */
    public function prepareEnvironmentVariables(\SimpleXMLElement $node)
    {
        $environmentVariables = array();
        if ($node->environmentVariables) {
            foreach ($node->environmentVariables->environmentVariable as $environmentVariableNode) {
                // Cut of the SimpleXML attributes wrapper and attach it to our environment variable
                $environmentVariable = (array)$environmentVariableNode;
                $environmentVariables[] = array_shift($environmentVariable);
            }
        }
        return $environmentVariables;
    }

    /**
     * Prepares the authentications array based on a simple xml element node
     *
     * @param \SimpleXMLElement $node The xml node
     *
     * @return array
     */
    public function prepareAuthentications(\SimpleXMLElement $node)
    {
        $authentications = array();
        if ($node->authentications) {
            foreach ($node->authentications->authentication as $authenticationNode) {
                $params = array();
                foreach ($authenticationNode->params->param as $paramNode) {
                    $paramName = (string)$paramNode->attributes()->name;
                    $params[$paramName] = (string)array_shift($authenticationNode->xpath(".//param[@name='$paramName']"));
                }
                $authentications[(string)$authenticationNode->attributes()->uri] = $params;
            }
        }
        return $authentications;
    }

    /**
     * Prepares the access array based on a simple xml element node
     *
     * @param \SimpleXMLElement $node The xml node
     *
     * @return array
     */
    public function prepareAccesses(\SimpleXMLElement $node)
    {
        // init accesses
        $accesses = array();
        if ($node->accesses) {
            foreach ($node->accesses->access as $accessNode) {
                $params = array();
                foreach ($accessNode->params->param as $paramNode) {
                    $paramName = (string)$paramNode->attributes()->name;
                    $params[$paramName] = (string)array_shift($accessNode->xpath(".//param[@name='$paramName']"));
                }
                $accesses[(string)$accessNode->attributes()->type][] = $params;
            }
        }
        return $accesses;
    }

    /**
     * Prepares the analytics array based on a simple XML element node
     *
     * @param \SimpleXMLElement $node The XML node
     *
     * @return array
     */
    public function prepareAnalytics(\SimpleXMLElement $node)
    {
        $analytics = array();
        if ($node->analytics) {
            foreach ($node->analytics->analytic as $analyticNode) {
                $connectors = array();
                foreach ($analyticNode->connectors->connector as $connectorNode) {
                    // connectors might have params
                    $params = array();
                    if ($connectorNode->params) {
                        foreach ($connectorNode->params->param as $paramNode) {
                            $paramName = (string)$paramNode->attributes()->name;
                            $params[$paramName] = (string)array_shift($connectorNode->xpath(".//param[@name='$paramName']"));
                        }
                    }

                    // build up the connectors entry
                    $connectors[] = array(
                        'name' => (string)$connectorNode->attributes()->name,
                        'type' => (string)$connectorNode->attributes()->type,
                        'params' => $params
                    );
                }

                // build up the analytics entry
                $analytics[] = array(
                    'uri' => (string)$analyticNode->attributes()->uri,
                    'connectors' => $connectors
                );
            }
        }
        return $analytics;
    }


    /**
     * Return's name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return's type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Return's logger name
     *
     * @return string
     */
    public function getLoggerName()
    {
        return $this->loggerName;
    }

    /**
     * Return's transport
     *
     * @return string
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * Returns rewrites
     *
     * @return array
     */
    public function getRewrites()
    {
        return $this->rewrites;
    }

    /**
     * Return's address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Return's port
     *
     * @return int
     */
    public function getPort()
    {
        return (int)$this->port;
    }

    /**
     * Return's software
     *
     * @return string
     */
    public function getSoftware()
    {
        return $this->software;
    }

    /**
     * Return's admin
     *
     * @return string
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * Return's analytics
     *
     * @return string
     */
    public function getAnalytics()
    {
        return $this->analytics;
    }

    /**
     * Return's keep-alive max connection
     *
     * @return int
     */
    public function getKeepAliveMax()
    {
        return (int)$this->keepAliveMax;
    }

    /**
     * Return's keep-alive timeout
     *
     * @return int
     */
    public function getKeepAliveTimeout()
    {
        return (int)$this->keepAliveTimeout;
    }

    /**
     * Return's template path for errors page
     *
     * @return string
     */
    public function getErrorsPageTemplatePath()
    {
        return $this->errorsPageTemplatePath;
    }

    /**
     * Returns template path for possible configured welcome page
     *
     * @return string
     */
    public function getWelcomePageTemplatePath()
    {
        return $this->welcomePageTemplatePath;
    }

    /**
     * Returns template path for possible configured auto index page
     *
     * @return string
     */
    public function getAutoIndexTemplatePath()
    {
        return $this->autoIndexTemplatePath;
    }

    /**
     * Return's worker number
     *
     * @return int
     */
    public function getWorkerNumber()
    {
        return (int)$this->workerNumber;
    }

    /**
     * Return's worker's accept min count
     *
     * @return int
     */
    public function getWorkerAcceptMin()
    {
        return (int)$this->workerAcceptMin;
    }

    /**
     * Return's worker's accept max count
     *
     * @return int
     */
    public function getWorkerAcceptMax()
    {
        return (int)$this->workerAcceptMax;
    }

    /**
     * Return's the auto index configuration
     *
     * @return boolean
     */
    public function getAutoIndex()
    {
        return (boolean)$this->autoIndex;
    }

    /**
     * Return's server context type
     *
     * @return string
     */
    public function getServerContextType()
    {
        return $this->serverContextType;
    }
    
    /**
     * Returns stream context type
     *
     * @return string
     */
    public function getStreamContextType()
    {
        return $this->streamContextType;
    }

    /**
     * Return's server context type
     *
     * @return string
     */
    public function getRequestContextType()
    {
        return $this->requestContextType;
    }

    /**
     * Return's socket type
     *
     * @return string
     */
    public function getSocketType()
    {
        return $this->socketType;
    }

    /**
     * Return's worker type
     *
     * @return string
     */
    public function getWorkerType()
    {
        return $this->workerType;
    }

    /**
     * Return's document root
     *
     * @return string
     */
    public function getDocumentRoot()
    {
        return $this->documentRoot;
    }

    /**
     * Return's directory index definition
     *
     * @return string
     */
    public function getDirectoryIndex()
    {
        return $this->directoryIndex;
    }

    /**
     * Return's the connection handlers
     *
     * @return array
     */
    public function getConnectionHandlers()
    {
        return $this->connectionHandlers;
    }
    
    /**
     * Returns the headers used by the server
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Returns the certificates used by the server
     *
     * @return array
     */
    public function getCertificates()
    {
        return $this->certificates;
    }
    
    /**
     * Return's the virtual hosts
     *
     * @return array
     */
    public function getVirtualHosts()
    {
        return $this->virtualHosts;
    }

    /**
     * Return's the authentication information's
     *
     * @return array
     */
    public function getAuthentications()
    {
        return $this->authentications;
    }

    /**
     * Return's modules
     *
     * @return array
     */
    public function getModules()
    {
        return $this->modules;
    }

    /**
     * Return's array
     *
     * @return array
     */
    public function getHandlers()
    {
        return $this->handlers;
    }

    /**
     * Return's cert path
     *
     * @return string
     */
    public function getCertPath()
    {
        return $this->certPath;
    }

    /**
     * Return's passphrase
     *
     * @return string
     */
    public function getPassphrase()
    {
        return $this->passphrase;
    }

    /**
     * Returns the environment variable configuration
     *
     * @return array
     */
    public function getEnvironmentVariables()
    {
        return $this->environmentVariables;
    }

    /**
     * Returns the access configuration.
     *
     * @return array
     */
    public function getAccesses()
    {
        return $this->accesses;
    }

    /**
     * Returns the locations.
     *
     * @return array
     */
    public function getLocations()
    {
        return $this->locations;
    }

    /**
     * Returns the rewrite maps.
     *
     * @return array
     */
    public function getRewriteMaps()
    {
        return $this->rewriteMaps;
    }
}
