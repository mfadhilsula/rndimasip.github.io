////////////////////////////////////////////////////////////////////////
//
// typeType.java
//
// This file was generated by XMLSpy 2008r2 Enterprise Edition.
//
// YOU SHOULD NOT MODIFY THIS FILE, BECAUSE IT WILL BE
// OVERWRITTEN WHEN YOU RE-RUN CODE GENERATION.
//
// Refer to the XMLSpy Documentation for further details.
// http://www.altova.com/xmlspy
//
////////////////////////////////////////////////////////////////////////

package gr.ntua.ivml.mint.oaiexporter.xml.schema.ESE_V3_2;


public class typeType extends com.altova.xml.TypeBase
{
		public static com.altova.xml.meta.ComplexType getStaticInfo() { return new com.altova.xml.meta.ComplexType(gr.ntua.ivml.mint.oaiexporter.xml.schema.ESE_V3_2.ESE_V3_2_TypeInfo.binder.getTypes()[gr.ntua.ivml.mint.oaiexporter.xml.schema.ESE_V3_2.ESE_V3_2_TypeInfo._altova_ti_altova_typeType]); }
	
	public typeType(org.w3c.dom.Node init)
	{
		super(init);
		instantiateMembers();
	}
	
	private void instantiateMembers()
	{

	}
	// Attributes
	public String getValue() 
	{ 
		com.altova.typeinfo.MemberInfo member = gr.ntua.ivml.mint.oaiexporter.xml.schema.ESE_V3_2.ESE_V3_2_TypeInfo.binder.getMembers()[gr.ntua.ivml.mint.oaiexporter.xml.schema.ESE_V3_2.ESE_V3_2_TypeInfo._altova_mi_altova_typeType._unnamed];
		return (String)com.altova.xml.XmlTreeOperations.castToString(getNode(), member);
	}
	
	public void setValue(String value)
	{
		com.altova.typeinfo.MemberInfo member = gr.ntua.ivml.mint.oaiexporter.xml.schema.ESE_V3_2.ESE_V3_2_TypeInfo.binder.getMembers()[gr.ntua.ivml.mint.oaiexporter.xml.schema.ESE_V3_2.ESE_V3_2_TypeInfo._altova_mi_altova_typeType._unnamed];
		com.altova.xml.XmlTreeOperations.setValue(getNode(), member, value);
	}
	


	// Elements
}
