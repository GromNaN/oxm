<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\OXM\Mapping\Driver;

use \Doctrine\OXM\Mapping\ClassMetadataInfo;
    
/**
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link    www.doctrine-project.org
 * @since   2.0
 * @version $Revision$
 * @author  Richard Fullmer <richard.fullmer@opensoftdev.com>
 * @author  Jérôme Tamarelle <jerome@tamarelle.net>
 */
class YamlDriver extends AbstractFileDriver
{

    /**
     * {@inheritdoc}
     */
    protected $_fileExtension = '.oxm.yml';

    /**
     * Loads the mapping for the specified class into the provided container.
     *
     * @param string $className
     * @param Mapping $mapping
     */
    public function loadMetadataForClass($className, ClassMetadataInfo $mapping)
    {
        $reflClass = $metadata->getReflectionClass();
        $element = $this->getElement($className);

        if('XmlRootEntity' == $element['type']) {
            $metadata->isRoot = true;
        } else if ('XmlMappedSuperclass' == $element['type']) {
            $metadata->isMappedSuperclass = true;
        }

        $metadata->setName($reflClass->getName());

        if (isset($element['xml'])) {
            $metadata->setXmlName($element['xml']);
        } else {
            $metadata->setXmlName(Inflector::xmlize($reflClass->getShortName()));
        }

        if (isset($element['repositoryClass'])) {
            $metadata->setCustomRepositoryClass($element['repositoryClass']);
        }

        if (isset($element['changeTrackingPolicy'])) {
            $changeTracking = $element['changeTrackingPolicy'];
            $metadata->setChangeTrackingPolicy(constant('Doctrine\OXM\Mapping\ClassMetadata::CHANGETRACKING_' . $changeTracking));
        }

        if (isset($element['namespace'])) {
            $xmlNamespaces = array($element['namespace']);
        } else if (isset($element['namespaces'])) {
            $xmlNamespaces = $element['namespaces'];
        }
        $metadata->setXmlNamespaces($xmlNamespaces);

        if (isset($element['fields'])) {
            foreach($element['fields'] as $fieldName => $field) {
                $mapping = $field;
                $mapping['fieldName'] = $fieldName;

                if (!isset($field['type'])) {
                    throw MappingException::propertyTypeIsRequired($className, $fieldName);
                }

                $metadata->mapField($mapping);
            }
        }

        if (isset($element['lifecycleCallbacks'])) {
            foreach($element['lifecycleCallbacks'] as $event => $methods) {
                foreach($methods as $method) {
                    $metadata->addLifecycleCallback($method, $event);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function _loadMappingFile($file)
    {
        return \Symfony\Component\Yaml\Yaml::load($file);
    }
}
