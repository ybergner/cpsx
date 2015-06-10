"""TO-DO: Write a description of what this XBlock is."""


import pkg_resources
import datetime

# from xblock.fields import Integer, Scope, String, Any, Boolean, Dict
from xblock.core import XBlock
from xblock.fields import Scope, Integer, String
from xblock.fragment import Fragment
from xmodule.fields import RelativeTime

class IBLTutoringChat(XBlock):
    """
    TO-DO: document what your XBlock does.
    """

    display_name = String(display_name="Display Name", default="IBL Tutoring Chat", scope=Scope.settings, help="Name of the component in the edxplatform")

    # TO-DO: delete count, and define your own fields.
    debug_mode		= String(display_name="debug", default="0", scope=Scope.content, help="Enable debug mode")	
    form_text 		= String(display_name="button_description", default=" ", scope=Scope.content, help="Button text description")	
    wait_time 		= String(display_name="button_description", default=" ", scope=Scope.content, help="Button text description")	
    required_price	= String(display_name="price_value", default="0", scope=Scope.content, help="Price")	
    n_course_id 	= String(display_name="CourseId", default="0", scope=Scope.user_state, help="Id of the current course")	
    n_user_id 		= String(display_name="UserId", default="0", scope=Scope.user_state, help="Id of the current user")	
	
    #provider data hardcored
    claim_prov_name	 = 'CyberSource'
    claim_prov_usr  	= String(display_name="ProviderUSER", default="cybersource-demo", scope=Scope.content, help="CyberSource key user")
    claim_prov_pwd  	= String(display_name="ProviderPass", default="cybrsource-demo-pwd", scope=Scope.content, help="CyberSource key pass")
    claim_prov_url  	= 'http://cybersource.com'

    #user data	
    claim_name 		= String(display_name="ClaimUserName", default="Jhon", scope=Scope.user_state, help="")	
    claim_mail 		= String(display_name="ClaimUserMail", default="sils@iblstudios.com", scope=Scope.user_state, help="")	
    claim_db_user_data 	= 'None'
    claim_db_user_id 	= 'None'
    claim_db_user_course= 'None'
    claim_db_user_name 	= 'None'
    claim_db_user_email = 'None'

    #control errors
    claim_errors = ''

    def resource_string(self, path):
        """Handy helper for getting resources from our kit."""
        data = pkg_resources.resource_string(__name__, path)
        return data.decode("utf8")

    # TO-DO: change this view to display your data your own way.
    def student_view(self, context):
	"""
	Get token provider
	"""
	self.n_user_id = self.get_student_id()
	self.claim_db_user_data = self.DB_get_user_data() 
	self.claim_db_user_id = self.claim_db_user_data[0] 
	self.claim_db_user_course = self.claim_db_user_data[1] 
	self.claim_db_user_name = self.claim_db_user_data[2] 
	self.claim_db_user_email = self.claim_db_user_data[3] 
	
	claim_name = self.claim_db_user_name
	claim_mail = self.claim_db_user_email
	
	self.claim_errors = ""

        """
        The primary view of the CsPayButton, shown to students
        when viewing courses.
        """
	import cspaybuild
	self.form_send_pay = cspaybuild.build_cb_payment(self.required_price,self.claim_db_user_course,self.claim_db_user_course,self.claim_db_user_id,self.claim_db_user_email,self.form_text,self.debug_mode)

	if self.claim_errors == "":
		if self.debug_mode == "1":
			html = self.resource_string("static/html/debug.html")
		else:
			html = self.resource_string("static/html/ibltutoring.html")

		frag = Fragment(html.format(self=self))
		frag.add_css(self.resource_string("static/css/style.css"))
		#frag.add_javascript(self.resource_string("static/js/src/cspaybutton.js"))
		#frag.initialize_js('CsPayButton')
		#frag.initialize_js('CsPaySubmit')
	else:
		html = self.resource_string("static/html/errors.html")
		frag = Fragment(html.format(self=self))
		frag.add_css(self.resource_string("static/css/style.css"))
        return frag

    def get_student_id(self):
        if hasattr(self, "xmodule_runtime"):
            s_id = self.xmodule_runtime.anonymous_student_id  # pylint:disable=E1101
        else:
            if self.scope_ids.user_id is None:
                s_id = "None"
            else:
                s_id = unicode(self.scope_ids.user_id)
	return s_id

    def DB_get_user_data(self):

        import appmysqldb, CommonFunc

	user_id		= "None"
	course_id  	= "None"
	user_name	= "None"
	user_email	= "None"

        db = appmysqldb.mysql('localhost', 3306, 'edxapp', 'root', '')
        q = "SELECT id, user_id, course_id FROM student_anonymoususerid WHERE anonymous_user_id='" + self.n_user_id + "'"
        CommonFunc.debug("QUERY: %s" %(q))
        db.query(q)
        res = db.fetchall()
	for row in res:
                user_id   = row[1]
                course_id = row[2]


	q = "SELECT name FROM auth_userprofile WHERE user_id='%s' " % (user_id)
        CommonFunc.debug("QUERY: %s" %(q))
        db.query(q)
        res = db.fetchall()
        for row in res:
                user_name   = row[0]


	q = "SELECT username FROM auth_user WHERE id='%s' " % (user_id)
        CommonFunc.debug("QUERY: %s" %(q))
        db.query(q)
        res = db.fetchall()
        for row in res:
                user_email   = row[0]

	
	results = [user_id,course_id,user_name,user_email]
        return results


    def studio_view(self, context=None):
        """
        The primary view of the CsPayButton, shown to students
        when viewing courses.
        """
	html = self.resource_string("static/html/ibltutoring_edit.html")
	frag = Fragment(html.format(self=self))
        frag.add_css(self.resource_string("static/css/style.css"))
        frag.add_javascript(self.resource_string("static/js/src/ibltutoringchat_edit.js"))
        frag.initialize_js('ibltutoringchatEdit')
        return frag


    @XBlock.json_handler
    def student_claim_save(self,claimdata,suffix=''):
	#parse data to claim badge
	import json
	award_result = 'error'
	return { 'result' :  award_result }

    @XBlock.json_handler
    def studio_save(self, data, suffix=''):
        """
        Called when submitting the form in Studio.
        """
        self.form_text      = data['form_text']
        self.required_price = data['required_price']
        self.wait_time = data['wait_time']
	if self.required_price =='':
		self.required_price = 0
        return {
            'result': 'success',
        }

    # TO-DO: change this to create the scenarios you'd like to see in the
    # workbench while developing your XBlock.
    @staticmethod
    def workbench_scenarios():
        """A canned scenario for display in the workbench."""
        return [
            ("CsPayButton",
             """<vertical_demo>
                <cspaybutton/>
                <cspaybutton/>
                <cspaybutton/>
                </vertical_demo>
             """),
        ]
