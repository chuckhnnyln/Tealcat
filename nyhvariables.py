# nyhvariables.py
# variable elements for the nyh collection data can be found here

def collection_url(alias, title):
	# Ryan and Chuck, this is where the url is build for the collection URL. Change as necessary
	return 'https://cdm16694.contentdm.oclc.org/digital/collection/' + alias + '/search/searchterm/' + title + '/field/relatig/mode/exact/conn/and/order/date'

def namespaces():
	return {
		'oai_pre' 			: '{http://www.openarchives.org/OAI/2.0/}'
		, 'dc_pre' 			: '{http://purl.org/dc/elements/1.1/}'
		, 'list_records' 	: '{http://www.openarchives.org/OAI/2.0/}ListRecords'
		, 'record' 			: '{http://www.openarchives.org/OAI/2.0/}record'
		, 'header' 			: '{http://www.openarchives.org/OAI/2.0/}header'
		, 'metadata' 		: '{http://www.openarchives.org/OAI/2.0/}metadata'
		, 'dc' 				: '{http://www.openarchives.org/OAI/2.0/oai_dc/}dc'
		, 'identifier' 		: '{http://purl.org/dc/elements/1.1/}identifier'
		, 'subject' 		: '{http://purl.org/dc/elements/1.1/}subject'
		, 'creator' 		: '{http://purl.org/dc/elements/1.1/}creator'
		, 'location' 		: '{http://purl.org/dc/elements/1.1/}location'
		, 'hidden_date'		: '{http://purl.org/dc/elements/1.1/}date'
	}

def topics():
	return [
		'Agriculture'
		, 'Architecture'
		, 'Arts & Entertainment'
		, 'Business & Industry'
		, 'Community & Events'
		, 'Daily Life'
		, 'Education'
		, 'Environment & Nature'
		, 'Ethnic Groups'
		, 'Geography & Maps'
		, 'Government, Law & Politics'
		, 'Medicine, Science & Technology'
		, 'Military & War"'
		, 'People'
		, 'Philosophy & Religion'
		, 'Recreation & Sports'
		, 'Transportation'
		, 'Work & Labor'
	]

def exist_paths():
	return {
		'collection_list' 	: 'http://54.174.162.83:8080/exist/apps/nyheritage/views/collection_list.xq'
		, 'collection_url'	: 'http://54.174.162.83:8080/exist/rest/db/apps/nyheritage/data/coll_%s.xml'
	}

def local_paths():
	return {
		'oai_output'	: '/home/ubuntu/nyh_scripts/Tealcat/output.xml'
		, 'processed'	: '/home/ubuntu/nyh_scripts/Tealcat/output/coll_%s.xml'
	}
