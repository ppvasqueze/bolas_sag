#!/usr/bin/env python2.7
"""
DESCRIPTION

   Inicializar el sistema, haciendo la aplicacion correr como un servicio.
   Leer los parametros del archivo de configuracion.
   Establecer las conexiones a la base de datos.
   Crear el socket para el proceso de las peticiones.
   Crear los subprocesos que controlan los eventos de asterisk.

EXAMPLES

    ./astqad.py [start] [stop] [restart] [reload]
    

EXIT STATUS

    Inicia o detiene el servicio de comunicacion Asterisk-QAD

AUTHOR

    CL3K LTDA <contacto@cl3k.com>

LICENSE
    This code is licensed under the terms of the 
    Contractual License Program (CLP) to 


VERSION

    $Id$
"""

import sys, time
import asyncore
from datetime import datetime
from daemon import Daemon
from socketServer import SocketServer
from webSocketServer import WebSocketServer
from webSocketServer import WSEchoHandler
from multiprocessing import Process
from ConfigParser import SafeConfigParser
import signal
import threading

from SimpleWebSocketServer import WebSocket
from SimpleWebSocketServer import SimpleWebSocketServer




class WSServerThread( threading.Thread ):
	"""
	La clase cliente socket que se levanta como un hilo de proceso 
	independiente.
	"""
	def __init__ ( self, sktServer):
		"""
		Inicializacion de la clase
		"""
		self.wshandlerclass = ''
		self.sktServer = sktServer
		self.wSktServer = SimpleWebSocketServer('', 9000, SimpleEcho)
		threading.Thread.__init__ ( self )


	def setWSHandlerClass(self, instance):
		self.wshandlerclass = instance


	def run ( self ):
		"""
		Una vez inicializada la clase esta es ejecutada en este metodo
		"""
		self.wSktServer.setInvoker(self)
		self.sktServer.attach(self)
		self.wSktServer.serveforever()

	def sendMessage(self, data):
		if self.wshandlerclass != '':
			self.wshandlerclass.send_bytes(data)

class SimpleEcho(WebSocket):
	
	def handleMessage(self):
		if self.data is None:
			self.data = ''
			# echo message back to client
		self.sendMessage(str(self.data))

	def handleConnected(self):
		print self.address, 'connected'

	def handleClose(self):
		print self.address, 'closed'

	def send_bytes(self, bytes):
		sys.stderr.write("\nenviando data desde WSsocket " + str(datetime.now()) + "\n")
		self.sendMessage(str(bytes))
		


class MyDaemon(Daemon):
	
	def handleSigUSR1(self, r, w):
		"""
		Procesa el reload como un cambio de senal del sistema
		"""
		sys.stderr.write("\nRELOAD THE PARAMETERS " + str(datetime.now()) + "\n")
		

	def reload(self):
		"""
		Recarga la data de la configuracion
		"""


	def run(self):
		signal.signal(signal.SIGUSR1, self.handleSigUSR1)    

		sys.stderr.write("\nrun " + str(datetime.now()) + "\n")
		
		sktServer = SocketServer('0.0.0.0', 9001)
		wSktServer = WSServerThread(sktServer).start()
		#wSktServer = WebSocketServer(port=9000, handlers={"/proxychannel": WSEchoHandler})
		asyncore.loop()
		sys.stderr.write("\ndespues del loop " + str(datetime.now()) + "\n")


if __name__ == "__main__":
	daemon = MyDaemon('/tmp/proxysws.pid')
	if len(sys.argv) == 2:
		if 'start' == sys.argv[1]:
			daemon.start()
		elif 'stop' == sys.argv[1]:
			daemon.stop()
		elif 'restart' == sys.argv[1]:
			daemon.restart()
		elif 'reload' == sys.argv[1]:
			daemon.reload()
		else:
			print "Unknown command"
			sys.exit(2)
		sys.exit(0)
	else:
		print "usage: %s start|stop|restart|reload" % sys.argv[0]
		sys.exit(2)
