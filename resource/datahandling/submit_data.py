#! /bin/python

import urllib2

protocol = "http://" # Change this if swicthing to https. 
domain = "localhost/sync/Orokonui_current/" # Change this went website domain changes, always end in /
path = "resource/datahandling/get.php" # Change this if the website structure changes.

def send_data(lat, lng, stype, svalue, time):
	# Get passcode from file
	f = open(".pass")
	passcode = int(f.read())
	f.close()
	# Build the url
	url = protocol + domain + path + "?stage=2"
	url += "&lat=" + str(lat) + "&lng=" + str(lng) + "&type=" + str(stype) + "&value=" + str(svalue) + "&time=" + str(time)
	# Get the public key
	pubkeys = urllib2.urlopen(protocol + domain + path + "?stage=1")
	n = pubkeys.read().split(",")[0]
	e = pubkeys.read().split(",")[0]
	# Encyrpt the passcode
	e_passcode = ((passcode**e)%n)
	# append encyrpted passcode to submit url
	url += "&pass=" + str(epasscode)
	# Send
	response = urllib2.urlopen(url)
	if response.read() != "success":
		#start debug and retry
		pass

send_data(1,1,1,1,1)
