import requests
import datetime
import time

get_url = 'http://roleplay.mrzs.eu/_get_info.php'
rate = 3

def getData():
	r = requests.get( get_url ).json()
	return r

def saveData():
	data = getData()

	print("--------\nData:")
	for server in data:
		print( "{} : {}".format( server, data[ server ] ) )

	# Saving implementation...

	return True


while True:
	print( datetime.datetime.now().strftime( "%H:%M" ) )
	if int( datetime.datetime.now().strftime( "%M" ) ) % rate == 0:
		saveData()
	time.sleep( 60 )
