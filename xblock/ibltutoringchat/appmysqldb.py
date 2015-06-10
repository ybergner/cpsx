# -*- coding: utf-8 -*-#
#

import CommonFunc
import MySQLdb

#
# MySQL class
#
class mysql:
	"""
	MySQL class provides easy interaction with MySQL databases

	Usage:

	print appmysqldb.mysql.__doc__

	db = appmysqldb.mysql('localhost', 3306, 'anunciandoorg', 'midget', '')
	q = "SELECT category_id, category_id, name_es FROM categories ORDER BY name_es"
	CommonFunc.debug("QUERY: %s" %(q))
	db.query(q)
	res = db.fetchall()
	for row in res:
		category_id = row[0]
		name_es = row[1]
		#CommonFunc.debug("%d|%s" %(category_id, name_es) )
		sys.stdout.write('.'),

	print

	"""

	def __init__(self, db_host, db_port, db_name, db_user, db_pass):
		self.db_host = db_host
		self.db_port = db_port
		self.db_user = db_user
		self.db_pass = db_pass
		self.db_name = db_name
		self.dbh = None


	def connect(self):
		#CommonFunc.debug("Creating (or reusing) DB connection")
		#CommonFunc.debug("--- DB ---")
		CommonFunc.debug("Connectiong to DB")
		if self.dbh == None:
			CommonFunc.debug("Creating DB hablder DB handler")
			self.dbh = MySQLdb.connect(host=self.db_host, user=self.db_user, passwd=self.db_pass, db=self.db_name)
			CommonFunc.debug("Using DB hablder: %s" % (self.dbh))
		else:
			CommonFunc.debug("Reusing DB handler: %s" % (self.dbh))


	def query(self, query):
		self.connect()	
		self.cur = self.dbh.cursor()
		CommonFunc.debug("Executing query: %s" % (query))
		self.res = self.cur.execute(query)  
		#CommonFunc.debug( "Returned rows: %d" % (int(self.numrows())) )
		return self.res

	def numrows(self):
		return self.cur.rowcount

	def fetchall(self):
		CommonFunc.debug("Fetching all results")
		return self.cur.fetchall()

	def showConfig(self):
		CommonFunc.debug( "db_host : %s" % (self.db_host) )
		CommonFunc.debug( "db_name : %s" % (self.db_name) )
		CommonFunc.debug( "db_user : %s" % (self.db_user) )
		CommonFunc.debug( "db_pass : %s" % (self.db_pass) )

	def disconnect(self):
		CommonFunc.debug("Closing DB connection")
		self.dbh.close()
		CommonFunc.debug("Disconnected")

