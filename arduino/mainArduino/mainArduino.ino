#define BUFFER_SIZE 128
#define WAITING_MODE 3
#define RECEIVING_MODE 2
#define RUNNING_MODE 1
#define WEIRD_MODE 0
#define YES 1
#define NO 0

#define WHEEL_CENTER 1
#define WHEEL_LEFT 2
#define WHEEL_RIGHT 3

int sendCommand = 0;
int mode = 0;

const int leftButton =  7;
const int rightButton = 6;
const int upButton = 5;
const int downButton = 4;
const int modeLED0 = 8;
const int modeLED1 = 9;

int bufferData = 0;

int counter = 0x1111;
int commandBuffer[BUFFER_SIZE];

void setup()
{
  Serial.begin(115200);
  pinMode(leftButton, OUTPUT);  
  pinMode(rightButton, OUTPUT);
  pinMode(upButton, OUTPUT);
  pinMode(downButton, OUTPUT);
  pinMode(modeLED0, OUTPUT);
  pinMode(modeLED1, OUTPUT);
  counter = 0;
}

void loop()
{
  digitalWrite(leftButton, HIGH); 
  digitalWrite(rightButton, HIGH); 
  digitalWrite(upButton, HIGH); 
  digitalWrite(downButton, HIGH); 
  
  setupLEDs(WAITING_MODE);
 
  if (Serial.available() > 0) {
    setupLEDs(RECEIVING_MODE);
    int data = Serial.read();
    if(data - 0xD0 == 0)
    {
      Serial.println("START");
      bufferData = YES;
      return;
    }
    if(bufferData == 0)
    {
      return;
    }
    commandBuffer[counter] = data;
    counter++;
    if(data - 0xB0 == 0)
    {
      Serial.println("STOP");
      bufferData = NO;
      processCommands();
    }
  }
}

void setupLEDs(int newMode)
{
  mode = newMode;
  if(mode & 0x1)
  {
    digitalWrite(modeLED0, HIGH); 
  } else {
    digitalWrite(modeLED0, LOW);
  }
  if(mode & 0x2)
  {
    digitalWrite(modeLED1, HIGH); 
  } else {
    digitalWrite(modeLED1, LOW);
  }
}

void processCommands()
{
  Serial.println("Process Commands");   // send an initial string
  mode = RUNNING_MODE;
  setupLEDs(RUNNING_MODE);
  int nextCommand = commandBuffer[0];
  int commandData = commandBuffer[1];
  int commandPointer = 0;
  while (nextCommand != 0xB0 && commandPointer < BUFFER_SIZE)
  {
    Serial.println(nextCommand,HEX);
    Serial.println(commandData,HEX);
    switch(nextCommand)
    {
      case 1:
        goForward(commandData);
        break;
      case 2:
        goReverse(commandData);
        break;
      case 3:
        wheels(commandData);
        break;
      default:
        break;
    }
    commandPointer+=2;
    nextCommand = commandBuffer[commandPointer];
    commandData = commandBuffer[commandPointer+1];
  }
  counter = 0;
}

void goForward(int time)
{
  digitalWrite(downButton, HIGH);
  Serial.println("Go Forward");   // send an initial string
  digitalWrite(upButton, LOW);
  delay(time);
}
void goReverse(int time)
{
  Serial.println("Go Reverse");   // send an initial string
  digitalWrite(upButton, HIGH);
  digitalWrite(downButton, LOW);
  delay(time);
}

void wheels(int wheelDirection)
{
  Serial.println("Change direction");   // send an initial string
  switch(wheelDirection)
  {
    case WHEEL_CENTER: //Go forward
    default:
      Serial.println("Going Forward");
      digitalWrite(leftButton, HIGH);
      digitalWrite(rightButton, HIGH);
      break;
    case WHEEL_LEFT: //Go left
      Serial.println("Going Left");
      digitalWrite(rightButton, HIGH);
      digitalWrite(leftButton, LOW);
      break;
    case WHEEL_RIGHT: //Go right
      Serial.println("Going Right");
      digitalWrite(leftButton, HIGH);
      digitalWrite(rightButton, LOW);
      break;
  }
}
