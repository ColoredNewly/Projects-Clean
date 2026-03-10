
def remove_elements(lst):
    for i, item in enumerate(lst):
        if item == "-":
            return lst[i+2:]
    return []


with open("text.txt") as myfile:

    for line in myfile:
        brojac1 = 0
        for charr in line:
            brojac1+=1

        fakt = ""
        for char in line:
            if char!="-":
                fakt+=char
            else:
                break

        niza = []
        for charrr in line:
            niza.append(charrr)

        niza = remove_elements(niza)

        broj_niza = 0
        for i in niza:
            broj_niza+=1

        text1 = ""
        brojac2 = 0
        # for i in niza:
        #     if i != ".":
        #         text1+=i
        #         brojac2+=1
        #     else:
        #         break
        for i in range(0, broj_niza):
            if i > 3:
                if niza[i-3]==".":
                    brojac2+=1
                    break
            text1+=niza[i]
            brojac2+=1


        text2 = ""
        for i in range(brojac2, broj_niza):
            text2+=niza[i]

        break
    # print(fakt)
    # print(niza)
    # print(text1)
    # print(text2)

print(brojac1)

with open("text.txt", 'r') as file:
    text = file.read()
    Used_text = text[:brojac1]
    text = text[brojac1:]

with open("text.txt", "w") as file:
    file.write(text)
with open("usedText.txt", "a") as file:
    file.write(Used_text)


