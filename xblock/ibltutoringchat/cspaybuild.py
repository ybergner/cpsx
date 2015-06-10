#
# build cb payment
#
def build_cb_payment(amount_value,coursename,courseid,studentid,studentemail,btn_txt='Continue to payment',debug_mode='0'):
	import hmac, hashlib, base64, uuid

	#merchant params
	url_pay='https://secureacceptance.cybersource.com/oneclick/pay'
	access_key='8c019c0e68643ae8a5a7c998c479e29d'
	profile_id='6E479D5B-7519-49F5-B9C7-AC0828A3DE6A'
	secret_key='530535ab362b4a90a186004658628862e81fc0358f9042e58c5590a489768bfeed8a2449b3ae4ee8a2d8d165311f4e719177b945d55b4aea88b572308fdb547bd09428ad096d4ea3b657895005a0f3919738c94cf26f4c618b2aa8958d06f45d9ed717e225ba4423869c11da19abc2d2a97e3d7f75654344b25a920c7fe9bd08'

	#transaction params
	locale='en'
	currency='usd'
	transaction_type='sale'
	transaction_uuid=str( uuid.uuid1() )  #uniqueid
	payment_method='card'
	#reference data
	reference_number= str( str(courseid)+'-'+ str(studentid) + '-' + str(studentemail) ) 
	#billing data
	bill_to_address_country='US' #cb codes iso country
	bill_to_address_state='AR' #cb codes states
	bill_to_address_city=''

	#amount Format USD (#,###.##)
	if amount_value > 0:
		amount=str(amount_value)
		amount=amount.replace(',','')
	else:
		amount='0'

	#requrired transaction fields
	signed_field_names="access_key,profile_id,transaction_uuid,signed_field_names,unsigned_field_names,signed_date_time,locale,transaction_type,amount,currency,payment_method,reference_number"
	unsigned_field_names='bill_to_address_country,bill_to_address_state,bill_to_address_city';

	#date_signed special format UTC ISO 8601 Date	
	import time
	from datetime import datetime
	from pytz import timezone
	date_str		 = time.strftime("%Y-%m-%d %H:%M:%S", time.gmtime() )	
	datetime_obj	 = datetime.strptime(date_str, "%Y-%m-%d %H:%M:%S")
	datetime_obj_utc = datetime_obj.replace(tzinfo=timezone('UTC'))
	signed_date_time = str( datetime_obj_utc.strftime("%Y-%m-%dT%H:%M:%S") )+'Z'

	#required data to sign
	import collections
	arr_data_to_sign = collections.OrderedDict()
	arr_data_to_sign.update( { 'access_key' : access_key } )
	arr_data_to_sign.update( { 'profile_id' : profile_id } )
	arr_data_to_sign.update( { 'transaction_uuid' : transaction_uuid } )
	arr_data_to_sign.update( { 'signed_field_names' : signed_field_names } )
	arr_data_to_sign.update( { 'unsigned_field_names' : unsigned_field_names } )
	arr_data_to_sign.update( { 'signed_date_time' : signed_date_time } )
	arr_data_to_sign.update( { 'locale' : locale } )
	arr_data_to_sign.update( { 'transaction_type' : transaction_type } )
	arr_data_to_sign.update( { 'amount' : amount } )
	arr_data_to_sign.update( { 'currency' : currency } )
	arr_data_to_sign.update( { 'payment_method' : payment_method } )
	arr_data_to_sign.update( { 'reference_number' : reference_number } )

	#
	# Sign data and return form
	#

	#format data to sign
	data_to_sign = ','.join(['{}={}'.format(k,str(v)) for k,v in arr_data_to_sign.items()])
	#return data_to_sign

	#generate signature
	signature = ''
	if data_to_sign!='' and secret_key!='':
		signature = base64.b64encode(hmac.new(secret_key,data_to_sign,digestmod=hashlib.sha256).digest())

	#build dict to send and add signature
	arr_data_to_send = arr_data_to_sign
	arr_data_to_send.update( { 'bill_to_address_country' : bill_to_address_country } )
	arr_data_to_send.update( { 'bill_to_address_state' : bill_to_address_state } )
	arr_data_to_send.update( { 'bill_to_address_city' : bill_to_address_city } )
	arr_data_to_send.update( { 'signature' : signature } )

	#create form
	if debug_mode !='0':
		result = '<form action="%s" name="cb_payment_submit" id="cb_payment_submit" method="post">' % (url_pay)
		for (k,v) in arr_data_to_send.items():
			result += '<input type="text" name="%s" value="%s">' % (k,v)
		result += '<span class="submitbutton">%s</span>' % (btn_txt)
		result += "</form>"
	else:
		result = '<form action="%s" name="cb_payment_submit" id="cb_payment_submit" method="post">' % (url_pay)
		for (k,v) in arr_data_to_send.items():
			result += '<input type="hidden" name="%s" value="%s">' % (k,v)
		result += '<input type="submit" value="%s" class="submitbutton">' % (btn_txt)
		result += "</form>"
	
	return result
