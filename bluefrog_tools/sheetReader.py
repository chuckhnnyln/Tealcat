# Edit the institution id to include multiple institutions

import gspread
from oauth2client.service_account import ServiceAccountCredentials
import xml.etree.cElementTree as ET
from xml.etree.ElementTree import tostring
from xml.dom import minidom
from xml.sax.saxutils import escape
from xml.sax.saxutils import quoteattr
import codecs

def pretty_XML(xml):
	return minidom.parseString(ET.tostring(xml)).toprettyxml(indent='\t')

def output_collection_XML(data, id_column):
	if data[id_column] <> 'BLANK':
		root = ET.Element('Collection')
		root.set('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance')
		root.set('xsi:noNamespaceSchemaLocation', 'http://18.204.203.159/:8080/exist/apps/nyheritage/NYHeritage.xsd')
		root.set('CollectionID', data[id_column])

		ET.SubElement(root, 'Title').text = data[0]
		ET.SubElement(root, 'CollectionAlias').text = data[2]

		inst_list = data[3].split(';')
		for inst in inst_list:
			ET.SubElement(root, 'InstitutionID').text = inst.strip()

		ET.SubElement(root, 'CollectionURL').text = data[4]

		abs = ET.SubElement(root, 'Abstract')
		ET.SubElement(abs, 'div').text = data[5]

		ET.SubElement(root, 'Extent').text = data[6]
		ET.SubElement(root, 'DatesOfOriginal').text = data[7]

		time_period_list = data[8].split(';')
		for tp in time_period_list:
			ET.SubElement(root, 'TimePeriod').text = tp.strip()

		ET.SubElement(root, 'CreatorAttribution').text = data[9]

		subj_list = data[10].split(';')
		for subj in subj_list:
			ET.SubElement(root, 'Subject').text = subj.strip()

		nyh_list = data[11].split(';')
		for nyh in nyh_list:
			ET.SubElement(root, 'NYHTopic').text = nyh.strip()

		loc_list = data[12].split(';')
		for loc in loc_list:
			ET.SubElement(root, 'Location').text = loc.strip()

		bh = ET.SubElement(root, 'BiogHistory')
		ET.SubElement(bh, 'div').text = data[13]

		sac = ET.SubElement(root, 'ScopeAndContent')
		ET.SubElement(sac, 'div').text = data[14]

		ET.SubElement(root, 'PublisherOfDigital').text = data[15]
		ET.SubElement(root, 'LocationOfOriginals').text = data[16]

		sacs = ET.SubElement(root, 'ScopeAndContentSource')
		ET.SubElement(sacs, 'div').text = data[17]

		ET.SubElement(root, 'FindingAidURL').text = data[18]

		ET.SubElement(root, 'CollectionType').text = data[20]
		
		ET.SubElement(root, 'SampleImageURL').text = data[27]

		with codecs.open('.\output\coll_%s.xml'%data[id_column], 'w', encoding='utf8') as f:
			f.write(pretty_XML(root))

def output_institution_XML(data):
	root = ET.Element('Institution')
	root.set('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance')
	root.set('xsi:noNamespaceSchemaLocation', 'http://18.204.203.159:8080/exist/apps/nyheritage/NYHeritage.xsd')
	root.set('InstitutionID', data[0])
# 2 InstitutionName
	ET.SubElement(root, 'InstitutionName').text = data[2]

	# 1 ParentOrganization
	ET.SubElement(root, 'ParentOrganization').text = data[1]

	# 3 DepartmentName
	ET.SubElement(root, 'Department').text = data[3]

	contact = ET.SubElement(root, 'ContactInfo')
	# 4 ContactPerson
	ET.SubElement(contact, 'ContactPerson').text = data[4]
	# 5 ContactPhone
	ET.SubElement(contact, 'ContactPhone').text = data[5]
	# 6 ContactEmail
	ET.SubElement(contact, 'ContactEmail').text = data[6]
	# 7 Address1
	ET.SubElement(contact, 'Address1').text = data[7]
	# 8 Address2
	ET.SubElement(contact, 'Address2').text = data[8]
	# 9 City
	ET.SubElement(contact, 'City').text = data[9]
	# 10 State
	ET.SubElement(contact, 'State').text = data[10]
	# 11 Zip
	ET.SubElement(contact, 'Zip').text = data[11]
	# 12 County
	ET.SubElement(contact, 'County').text = data[12]
	# 13 Phone
	ET.SubElement(contact, 'Phone').text = data[13]
	# 14 Fax
	ET.SubElement(contact, 'Fax').text = data[14]
	# 15 Website
	ET.SubElement(contact, 'Website').text = data[15]
	# 16 About
	about = ET.SubElement(root, 'About')
	ET.SubElement(about, 'div').text = data[16]
	# 18 CouncilID
	ET.SubElement(root, 'CouncilID').text = data[18]
	# 17 LogoURL (leave blank for now)
	ET.SubElement(root, 'LogoURL').text = data[17]

	ET.SubElement(root, 'ProxyMember').text = data[20]
	
	displayName = data[2]
	if len(data[1]) > 0:
		displayName = '%s - %s' % (data[1], displayName)
	ET.SubElement(root, 'DisplayName').text = displayName
	
	#print tostring(root)

	with codecs.open('.\output\inst_%s.xml'%data[0], 'w', encoding='utf8') as f:
		f.write(pretty_XML(root))

def process_sheet(gc, sheet_key, coll_id_col, council):
	book = gc.open_by_key(sheet_key)

	wks = book.worksheet('Collection')
	row_count = len(wks.col_values(1))
	test = 0
	for i in range(2, row_count+1):
		title = wks.cell(i, 1).value.encode('ascii', 'replace').replace('\n', ' ')
		inst_id = wks.cell(i, 4).value.encode('ascii', 'replace').replace('\n', ' ')
		coll_id = wks.cell(i, 2).value.encode('ascii', 'replace').replace('\n', ' ')
		if len(title) > 1:
			print '%s\t%s\t%s\t%s' % (council, inst_id, coll_id, title)
			output_collection_XML(wks.row_values(i), coll_id_col)
		else:
			test += 1
		if test > 4:
			break

	wks = book.worksheet('Institution')
	row_count = len(wks.col_values(1))
	test = 0
	for i in range(2, row_count+1):
		inst_name = wks.cell(i, 3).value.encode('ascii', 'replace').replace('\n', ' ')
		council =  wks.cell(i, 19).value.encode('ascii', 'replace').replace('\n', ' ')
		if len(inst_name) > 1:
			print council, inst_name
			output_institution_XML(wks.row_values(i))
		else:
			test += 1
		if test > 4:
			break


scope = ['https://spreadsheets.google.com/feeds']
credentials = ServiceAccountCredentials.from_json_keyfile_name('nyheritage-ea5660f1cf23.json', scope)
gc = gspread.authorize(credentials)
process_sheet(gc, '1dcAcCYbqu7Zt3CQeS-_5zlzjXmPL28QPWzt3s5PuFqU', 1, 'cdlc') # CDLC
process_sheet(gc, '1lYVFrB75mMDSsGRcpbko81E21doF_7kWdTNtTSN_WDA', 1, 'clrc') # CLRC
process_sheet(gc, '1n6ez_S62s6yR46MxSSdjua0eP3BdqwdQLIqf5TkgKXI', 1, 'lilrc') # LILRC
process_sheet(gc, '1VlSKNwEtDEzQBD4ZihyCNeGfyvRUxjLA2C_-wSqt19c', 2, 'metro') # METRO
process_sheet(gc, '1a0oKoh3RnbMwenmd0yVzwxmHp9U23q0YaRLySNFeJBE', 1, 'nnyln') # NNYLN
process_sheet(gc, '1W_pub4sM1meiZmjkTtwWSp4JFTRP67aUx23OfFDWwBI', 1, 'rrlc') # RRLC
process_sheet(gc, '1mwS-vLJ7vb1EoeW-lf_CGOVqF4iBar9QEUnqdAnKmBA', 1, 'scrlc') # SCRLC
process_sheet(gc, '1af2HrMLLg0FIgF6cMiItplS--NyDENQrpxUzitmQ_k8', 1, 'wnylrc') # WNYLRC
