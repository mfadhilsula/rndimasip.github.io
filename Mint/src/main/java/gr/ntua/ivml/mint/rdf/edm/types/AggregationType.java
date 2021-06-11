//
// This file was generated by the JavaTM Architecture for XML Binding(JAXB) Reference Implementation, vJAXB 2.1.10 in JDK 6 
// See <a href="http://java.sun.com/xml/jaxb">http://java.sun.com/xml/jaxb</a> 
// Any modifications to this file will be lost upon recompilation of the source schema. 
// Generated on: 2011.05.04 at 01:49:42 PM EEST 
//


package gr.ntua.ivml.mint.rdf.edm.types;

import java.util.ArrayList;
import java.util.List;
import javax.xml.bind.annotation.XmlAccessType;
import javax.xml.bind.annotation.XmlAccessorType;
import javax.xml.bind.annotation.XmlElement;
import javax.xml.bind.annotation.XmlType;


/**
 * A set of related resources (Aggregated Resources), grouped together such
 * that the set can be treated as a single resource. This is the entity described
 * within the ORE interoperability framework by a Resource Map.
 * 
 * <p>Java class for AggregationType complex type.
 * 
 * <p>The following schema fragment specifies the expected content contained within this class.
 * 
 * <pre>
 * &lt;complexType name="AggregationType">
 *   &lt;complexContent>
 *     &lt;restriction base="{http://www.w3.org/2001/XMLSchema}anyType">
 *       &lt;sequence>
 *         &lt;element name="proxy" type="{http://www.example.org/EDMSchemaV9}ProxyType"/>
 *         &lt;element name="aggregatedCHO" type="{http://www.example.org/EDMSchemaV9}PhysicalThingType"/>
 *         &lt;element name="webResources" type="{http://www.example.org/EDMSchemaV9}WebWrapperType"/>
 *         &lt;element name="creator" type="{http://www.example.org/EDMSchemaV9}SimpleLiteral" maxOccurs="unbounded"/>
 *       &lt;/sequence>
 *     &lt;/restriction>
 *   &lt;/complexContent>
 * &lt;/complexType>
 * </pre>
 * 
 * 
 */
@XmlAccessorType(XmlAccessType.FIELD)
@XmlType(name = "AggregationType", propOrder = {
    "proxy",
    "aggregatedCHO",
    "webResources",
    "creator"
})
public class AggregationType {

    @XmlElement(required = true)
    protected ProxyType proxy;
    @XmlElement(required = true)
    protected PhysicalThingType aggregatedCHO;
    @XmlElement(required = true)
    protected WebWrapperType webResources;
    @XmlElement(required = true)
    protected List<SimpleLiteral> creator;

    /**
     * Gets the value of the proxy property.
     * 
     * @return
     *     possible object is
     *     {@link ProxyType }
     *     
     */
    public ProxyType getProxy() {
        return proxy;
    }

    /**
     * Sets the value of the proxy property.
     * 
     * @param value
     *     allowed object is
     *     {@link ProxyType }
     *     
     */
    public void setProxy(ProxyType value) {
        this.proxy = value;
    }

    /**
     * Gets the value of the aggregatedCHO property.
     * 
     * @return
     *     possible object is
     *     {@link PhysicalThingType }
     *     
     */
    public PhysicalThingType getAggregatedCHO() {
        return aggregatedCHO;
    }

    /**
     * Sets the value of the aggregatedCHO property.
     * 
     * @param value
     *     allowed object is
     *     {@link PhysicalThingType }
     *     
     */
    public void setAggregatedCHO(PhysicalThingType value) {
        this.aggregatedCHO = value;
    }

    /**
     * Gets the value of the webResources property.
     * 
     * @return
     *     possible object is
     *     {@link WebWrapperType }
     *     
     */
    public WebWrapperType getWebResources() {
        return webResources;
    }

    /**
     * Sets the value of the webResources property.
     * 
     * @param value
     *     allowed object is
     *     {@link WebWrapperType }
     *     
     */
    public void setWebResources(WebWrapperType value) {
        this.webResources = value;
    }

    /**
     * Gets the value of the creator property.
     * 
     * <p>
     * This accessor method returns a reference to the live list,
     * not a snapshot. Therefore any modification you make to the
     * returned list will be present inside the JAXB object.
     * This is why there is not a <CODE>set</CODE> method for the creator property.
     * 
     * <p>
     * For example, to add a new item, do as follows:
     * <pre>
     *    getCreator().add(newItem);
     * </pre>
     * 
     * 
     * <p>
     * Objects of the following type(s) are allowed in the list
     * {@link SimpleLiteral }
     * 
     * 
     */
    public List<SimpleLiteral> getCreator() {
        if (creator == null) {
            creator = new ArrayList<SimpleLiteral>();
        }
        return this.creator;
    }

}
