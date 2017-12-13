import gspread
from oauth2client.service_account import ServiceAccountCredentials
import xml.etree.cElementTree as ET
from collections import defaultdict
import sys
import urllib2
import datetime
import nyhvariables

nyh_topics = nyhvariables.topics()

oai_pre = nyhvariables.namespaces()['oai_pre']
dc_pre = nyhvariables.namespaces()['dc_pre'] 

list_records = nyhvariables.namespaces()['list_records']
record = nyhvariables.namespaces()['record']
header = nyhvariables.namespaces()['header']
metadata = nyhvariables.namespaces()['metadata']
dc = nyhvariables.namespaces()['dc']
identifier = nyhvariables.namespaces()['identifier']
subject = nyhvariables.namespaces()['subject']
creator = nyhvariables.namespaces()['creator']
location = nyhvariables.namespaces()['location']
hidden_date = nyhvariables.namespaces()['hidden_date']

list_length = 5

def add_to_list(array, value):
	found = False
	for item in array:
		if item[0] == value:
			found = True
			item[1] += 1
			break
	if not found:
		array.append([])
		array[-1].append(value)
        array[-1].append(1)

def showChildren(r):
	# This function is only used for debugging and isn't called normally
	#print '**********', r.tag, 'Start **********'
	for child in r:
		if child.tag == '{http://purl.org/dc/elements/1.1/}identifier' and child.text == 'XNC002':
			print child.tag, child.text.encode('ascii', 'ignore')

	#print '**********', r.tag, 'Stop **********'

def get_collections_list():
	collection_list = []
	coll_xml = urllib2.urlopen(nyhvariables.exist_paths()['collection_list'])
	e = ET.parse(coll_xml).getroot()
	for child in e.findall('Collection'):
		collection_list.append(child.get('CollectionID'))

	return collection_list

def get_metadata(collection_list):
	e = ET.parse(nyhvariables.local_paths()['oai_output']).getroot()
	nyh_metadata = defaultdict(dict)
	for listing in e.findall(list_records):
		for rec in listing.findall(record):
			head = rec.findall(header)
			if('status' not in head[0].attrib):
				for meta in rec.findall(metadata):
					for dc_elements in meta.findall(dc):
						id = dc_elements.findall(identifier)
						for i in id:
							coll = i.text.replace(";", "").rstrip()#.upper()
							if coll in collection_list:
								if coll not in nyh_metadata:
									#print("New collection found: \t%s" % coll)
									nyh_metadata[coll]['extent'] = 1
									nyh_metadata[coll]['creator'] = []
									nyh_metadata[coll]['subject'] = []
									nyh_metadata[coll]['nyh_topic'] = []
									nyh_metadata[coll]['location'] = []
									nyh_metadata[coll]['date'] = []

								else:
									nyh_metadata[coll]['extent'] += 1

								# Get the Subjects and NYH Topics
								for sub in dc_elements.findall(subject):
									subject_list = sub.text.split(';')
									for s in subject_list:
										s = s.strip().title()
										if s in nyh_topics:
											add_to_list(nyh_metadata[coll]['nyh_topic'], s)
										else:
											if len(s) > 0:
												add_to_list(nyh_metadata[coll]['subject'], s)

								# Get the Creator
								for cre in dc_elements.findall(creator):
									creator_list = cre.text.split(';')
									for c in creator_list:
										c = c.strip().title()
										if not c == 'Unknown':
											add_to_list(nyh_metadata[coll]['creator'], c)

								# Get the locations
								for loc in dc_elements.findall(location):
									location_list = loc.text.split(';')
									for l in location_list:
										l = l.strip().title()
										add_to_list(nyh_metadata[coll]['location'], l)
										
								# Get the dates
								for date_field in dc_elements.findall(hidden_date):
									date_list = date_field.text.split(';')
									for d in date_list:
										d = d.strip().title()
										#print d
										for s in d.split():
											if s.isdigit():
												if 1600 <= int(s) <= 2030:
													add_to_list(nyh_metadata[coll]['date'], (int(s)/10)*10)
													#print '%s|%s|%s' % (d, s, (int(s)/10)*10)
										
										

	return nyh_metadata


def get_list_from_set(s, delim):
	ret_val = ''
	for v in s:
		if len(v) > 0:
			ret_val += '%s%s ' % (v, delim)

	return ret_val

def get_field_from_array(array, length):
	ret_val = ''
	for a in array[:length]:
		tmp = '%s;' % a[0]
		ret_val = '%s%s' % (ret_val, tmp)

	return ret_val

def update_sheet(gc, sheet_key, nyh_metadata, council):
	book = gc.open_by_key(sheet_key)

	wks = book.worksheet('Collection')
	test = 0
	for i in range(2, len(wks.col_values(1))+1):
		title = wks.cell(i, 1).value.encode('ascii', 'replace').replace('\n', ' ')
		inst_id = wks.cell(i, 4).value.encode('ascii', 'replace').replace('\n', ' ')
		coll_id = wks.cell(i, 2).value.replace(";", "")

		if len(title) > 1:
			if coll_id in nyh_metadata:
				if nyh_metadata[coll_id]['extent'] == 1:
					extent = '%i item' % nyh_metadata[coll_id]['extent']
				else:
					extent = '%i items' % nyh_metadata[coll_id]['extent']

				creator = get_field_from_array(sorted(nyh_metadata[coll_id]['creator'], key=lambda x:[1], reverse=True), list_length)
				subject = get_field_from_array(sorted(nyh_metadata[coll_id]['subject'], key=lambda x:[1], reverse=True), list_length)
				location = get_field_from_array(sorted(nyh_metadata[coll_id]['location'], key=lambda x:[1], reverse=True), list_length)
				timePeriods = get_field_from_array(sorted(nyh_metadata[coll_id]['date'], key=lambda x:[1], reverse=True), list_length)
				print council, coll_id, inst_id, title, nyh_metadata[coll_id]['extent']

				wks.update_cell(i, 7, extent)
				wks.update_cell(i, 9, timePeriods)
				wks.update_cell(i, 10, creator)
				wks.update_cell(i, 11, subject)


		else:
			test += 1
		if test > 4:
			break

def getXML(element_id):
	url = nyhvariables.exist_paths()['collection_url'] % element_id
	file = urllib2.urlopen(url)
	return ET.parse(file)

def process_collections(nyh_collections):
	for coll_id in nyh_collections:
		#print("Processing collection \t%s" % coll_id)
		try:
			xml = getXML(coll_id)
			collection = xml.getroot()
			if nyh_collections[coll_id]['extent'] == 1:
				extent = '%i item' % nyh_collections[coll_id]['extent']
			else:
				extent = '%i items' % nyh_collections[coll_id]['extent']
			xmlExtent = collection.findall('Extent')
			for e in xmlExtent:
				collection.remove(e)
			ET.SubElement(collection, 'Extent').text = extent
			
			xmlCollectionURL = collection.findall('CollectionURL')
			if(len(xmlCollectionURL) < 1):
				collTitle = ''
				collAlias = ''
				# Get the title from the XML file
				xmlTitle = collection.findall('Title')
				for xt in xmlTitle:
					collTitle = xt.text.replace(' ', '%20')
				xmlCollAlias = collection.findall('CollectionAlias')
				for xc in xmlCollAlias:
					collAlias = xc.text
				
				collectionURL = nyhvariables.collection_url(collAlias, collTitle)

				ET.SubElement(collection, 'CollectionURL').text = collectionURL
			
			creator = get_field_from_array(sorted(nyh_collections[coll_id]['creator'], key=lambda x:[1], reverse=True), list_length)
			xmlCreator = collection.findall('CreatorAttribution')
			for c in xmlCreator:
				collection.remove(c)
			ET.SubElement(collection, 'CreatorAttribution').text = creator			
			
			subject = get_field_from_array(sorted(nyh_collections[coll_id]['subject'], key=lambda x:[1], reverse=True), list_length)
			xmlSubject = collection.findall('Subject')
			for s in xmlSubject:
				collection.remove(s)
			subj_list = subject.split(';')
			for subj in subj_list:
				ET.SubElement(collection, 'Subject').text = subj.strip()
			
			xmlLocation = collection.findall('Location')
			for l in xmlLocation:
				collection.remove(l)
			location = get_field_from_array(sorted(nyh_collections[coll_id]['location'], key=lambda x:[1], reverse=True), list_length)
			loc_list = location.split(';')
			for loc in loc_list:
				ET.SubElement(collection, 'Location').text = loc.strip()
				
			xmlTimePeriods = collection.findall('TimePeriod')
			for t in xmlTimePeriods:
				collection.remove(t)
			timePeriods = get_field_from_array(sorted(nyh_collections[coll_id]['date'], key=lambda x:[1], reverse=True), list_length)
			tp_list = timePeriods.split(';')
			for tp in tp_list:
				ET.SubElement(collection, 'TimePeriod').text = tp.strip()
            
			xml.write(nyhvariables.local_paths()['processed'] % coll_id)
		except:
			print("Unexpected error:", sys.exc_info())
	
	
def main(argv):
	collection_list = get_collections_list()
	nyh_collections = get_metadata(collection_list)
	process_collections(nyh_collections)
	
	
	
if __name__ == "__main__":
	main(sys.argv[1:])
