from moviepy.editor import VideoFileClip, CompositeAudioClip, AudioFileClip, TextClip, CompositeVideoClip
import random

count = 0
count2 = 0
with open("text222.txt", "r") as file:
    for line in file:

        lista = []
        for bukva in line:
            lista.append(bukva)

        for i in range(len(lista)):
            if lista[i] == "f" and lista[i+1] == "a" and lista[i+2] == "c" and lista[i+3] == "t":
                count+=1

        count2 +=1

print(count)
print(count2)
