////////////////////////////////////////////////////////////////////////
//
// metadataType.java
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


public class metadataType extends com.altova.xml.TypeBase
{
		public static com.altova.xml.meta.ComplexType getStaticInfo() { return new com.altova.xml.meta.ComplexType(gr.ntua.ivml.mint.oaiexporter.xml.schema.ESE_V3_2.ESE_V3_2_TypeInfo.binder.getTypes()[gr.ntua.ivml.mint.oaiexporter.xml.schema.ESE_V3_2.ESE_V3_2_TypeInfo._altova_ti_altova_metadataType]); }
	
	public metadataType(org.w3c.dom.Node init)
	{
		super(init);
		instantiateMembers();
	}
	
	private void instantiateMembers()
	{
		
		record= new MemberElement_record (this, gr.ntua.ivml.mint.oaiexporter.xml.schema.ESE_V3_2.ESE_V3_2_TypeInfo.binder.getMembers()[gr.ntua.ivml.mint.oaiexporter.xml.schema.ESE_V3_2.ESE_V3_2_TypeInfo._altova_mi_altova_metadataType._record]);
	}
	// Attributes


	// Elements
	
	public MemberElement_record record;

		public static class MemberElement_record
		{
			public static class MemberElement_record_Iterator implements java.util.Iterator
			{
				private org.w3c.dom.Node nextNode;
				private MemberElement_record member;
				public MemberElement_record_Iterator(MemberElement_record member) {this.member=member; nextNode=member.owner.getElementFirst(member.info);}
				public boolean hasNext() 
				{
					while (nextNode != null)
					{
						if (com.altova.xml.TypeBase.memberEqualsNode(member.info, nextNode))
							return true;
						nextNode = nextNode.getNextSibling();
					}
					return false;
				}
				
				public Object next()
				{
					recordType nx = new recordType(nextNode);
					nextNode = nextNode.getNextSibling();
					return nx;
				}
				
				public void remove () {}
			}
			protected com.altova.xml.TypeBase owner;
			protected com.altova.typeinfo.MemberInfo info;
			public MemberElement_record (com.altova.xml.TypeBase owner, com.altova.typeinfo.MemberInfo info) { this.owner = owner; this.info = info;}
			public recordType at(int index) {return new recordType(owner.getElementAt(info, index));}
			public recordType first() {return new recordType(owner.getElementFirst(info));}
			public recordType last(){return new recordType(owner.getElementLast(info));}
			public recordType append(){return new recordType(owner.createElement(info));}
			public boolean exists() {return count() > 0;}
			public int count() {return owner.countElement(info);}
			public void remove() {owner.removeElement(info);}
			public void removeAt(int index) {owner.removeElementAt(info, index);}
			public java.util.Iterator iterator() {return new MemberElement_record_Iterator(this);}
			public com.altova.xml.meta.Element getInfo() { return new com.altova.xml.meta.Element(info); }
		}
}